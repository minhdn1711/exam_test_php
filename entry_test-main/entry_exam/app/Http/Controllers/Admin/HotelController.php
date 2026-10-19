<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HotelRequest;
use App\Models\Hotel;
use App\Models\Prefecture;
use App\Services\HotelImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class HotelController extends Controller
{
    /** session key holding the edit draft between the confirm and complete steps */
    private const EDIT_DRAFT_KEY = 'hotel_edit';

    public function __construct(
        private readonly HotelImageService $images
    ) {
    }

    /** get methods */

    /**
     * Hotel list. The search conditions come from the query string set by searchResult(),
     * so that the result can be reloaded and paginated; all hotels are listed without conditions.
     */
    public function showSearch(Request $request): View|RedirectResponse
    {
        $hotelName = $request->query('hotel_name');
        $hotelName = is_string($hotelName) && $hotelName !== '' ? mb_substr($hotelName, 0, 255) : null;

        $prefectureId = $request->query('prefecture_id');
        $prefectureId = is_string($prefectureId) && ctype_digit($prefectureId) ? (int) $prefectureId : null;

        $hotelList = Hotel::getHotelListByName($hotelName, $prefectureId)->withQueryString();

        // e.g. the last hotel of the last page was deleted: show the new last page instead of an empty one
        if ($hotelList->isEmpty() && $hotelList->currentPage() > 1) {
            return redirect($hotelList->url($hotelList->lastPage()));
        }

        return view('admin.hotel.result', [
            'prefectures' => $this->getPrefectures(),
            'hotelList' => $hotelList,
            'isSearched' => $hotelName !== null || $prefectureId !== null,
        ]);
    }

    public function showCreate(): View
    {
        // after a validation error, show the image selected in the previous submit
        $keptImage = session()->has('errors') ? $this->images->kept('create') : null;
        if ($keptImage === null) {
            $this->images->forget();
        }

        return view('admin.hotel.create', [
            'prefectures' => $this->getPrefectures(),
            'keptImage' => $keptImage,
        ]);
    }

    /**
     * Edit step 1 (input screen) and step 3 (completion screen, right after a successful update)
     */
    public function showEdit(Request $request): View
    {
        $hotel = Hotel::with('prefecture')->findOrFail((int) $request->query('hotel_id'));

        if (session('hotel_edit_completed') === $hotel->hotel_id) {
            return view('admin.hotel.edit_complete', compact('hotel'));
        }

        $formKey = 'edit:' . $hotel->hotel_id;

        // coming back from the confirm screen: restore the entered values and image
        $draft = $request->boolean('back') ? $this->getDraft($hotel->hotel_id) : null;

        $keptImage = null;
        $draftImageTakenOver = false;
        if (session()->has('errors')) {
            $keptImage = $this->images->kept($formKey);
        } elseif ($draft !== null && $draft['tmp_image'] !== null) {
            $keptImage = $draft['tmp_image'];
            $this->images->keep($formKey, $keptImage);
            $draftImageTakenOver = true;
        }

        if ($keptImage === null) {
            $this->images->forget();
        }
        $this->discardDraft(deleteImage: !$draftImageTakenOver);

        return view('admin.hotel.edit', [
            'hotel' => $hotel,
            'prefectures' => $this->getPrefectures(),
            'hotelName' => old('hotel_name', $draft['hotel_name'] ?? $hotel->hotel_name),
            'prefectureId' => (int) old('prefecture_id', $draft['prefecture_id'] ?? $hotel->prefecture_id),
            'keptImage' => $keptImage,
        ]);
    }

    /** post methods */

    /**
     * Validate the search form, then redirect to the hotel list with the conditions (Post/Redirect/Get)
     */
    public function searchResult(Request $request): RedirectResponse
    {
        $hotelName = $request->input('hotel_name');
        $prefectureId = $request->input('prefecture_id');

        if (($hotelName === null || $hotelName === '') && ($prefectureId === null || $prefectureId === '')) {
            return redirect()
                ->route('adminHotelSearchPage')
                ->withErrors(['search' => '何も入力されていません']);
        }

        $request->validate([
            'hotel_name' => ['nullable', 'string', 'max:255'],
            'prefecture_id' => ['nullable', 'integer', 'exists:prefectures,prefecture_id'],
        ], [
            'hotel_name.max' => 'ホテル名は255文字以内で入力してください。',
            'prefecture_id.*' => '都道府県を正しく選択してください。',
        ]);

        return redirect()->route('adminHotelSearchPage', array_filter([
            'hotel_name' => $hotelName,
            'prefecture_id' => $prefectureId,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function create(HotelRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $filePath = $this->images->store($request->file('image'), Hotel::IMAGE_DIR);
            $this->images->forget();
        } else {
            $filePath = $this->images->publish($request->keptImage());
            $this->images->forget(deleteFile: false);
        }

        try {
            $hotel = Hotel::create([
                'hotel_name' => $validated['hotel_name'],
                'prefecture_id' => (int) $validated['prefecture_id'],
                'file_path' => $filePath,
            ]);
        } catch (Throwable $e) {
            $this->images->delete($filePath);
            throw $e;
        }

        Alert::success('登録が完了しました', '「' . $hotel->hotel_name . '」を登録しました。');

        return redirect()->route('adminHotelSearchPage');
    }

    /**
     * Edit: the input screen posts step=confirm (step 2: confirmation screen),
     * the confirmation screen posts step=save (save, then step 3: completion screen)
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->input('step') === 'save') {
            return $this->saveEdit((int) $request->input('hotel_id'));
        }

        // resolving the form request runs its validation (redirects back with errors on failure)
        return $this->confirmEdit(app(HotelRequest::class));
    }

    public function delete(Request $request): RedirectResponse
    {
        $hotel = Hotel::find((int) $request->input('hotel_id'));
        if ($hotel === null) {
            Alert::error('削除できませんでした', '削除対象のホテルが見つかりませんでした。');

            return back();
        }

        $hotelName = $hotel->hotel_name;
        $uploadedImage = $hotel->hasUploadedImage() ? $hotel->file_path : null;

        // related bookings are removed by the ON DELETE CASCADE foreign key
        $hotel->delete();
        $this->images->delete($uploadedImage);

        Alert::success('削除が完了しました', '「' . $hotelName . '」を削除しました。');

        return back();
    }

    /** helpers */

    private function getPrefectures()
    {
        return Prefecture::orderBy('prefecture_id')->get(['prefecture_id', 'prefecture_name']);
    }

    /**
     * Edit step 2: keep the entered values in the session and show the confirmation screen
     */
    private function confirmEdit(HotelRequest $request): View
    {
        $hotel = Hotel::with('prefecture')->findOrFail((int) $request->input('hotel_id'));
        $validated = $request->validated();

        $this->discardDraft();

        // a new image is saved to the tmp directory until the edit is confirmed
        if ($request->hasFile('image')) {
            $tmpImage = $this->images->store($request->file('image'), HotelImageService::TMP_DIR);
            $this->images->forget();
        } else {
            $tmpImage = $request->keptImage();
            $this->images->forget(deleteFile: $tmpImage === null);
        }

        $draft = [
            'hotel_id' => $hotel->hotel_id,
            'hotel_name' => $validated['hotel_name'],
            'prefecture_id' => (int) $validated['prefecture_id'],
            'tmp_image' => $tmpImage,
        ];
        session([self::EDIT_DRAFT_KEY => $draft]);

        return view('admin.hotel.edit_confirm', [
            'hotel' => $hotel,
            'draft' => $draft,
            'prefecture' => Prefecture::find($draft['prefecture_id']),
        ]);
    }

    /**
     * Save the confirmed edit and redirect to the completion screen
     */
    private function saveEdit(int $hotelId): RedirectResponse
    {
        $draft = $this->getDraft($hotelId);
        if ($draft === null) {
            Alert::error('更新できませんでした', '入力内容の有効期限が切れました。もう一度入力してください。');

            return redirect()->route('adminHotelEditPage', ['hotel_id' => $hotelId]);
        }

        $hotel = Hotel::findOrFail($hotelId);
        $oldImage = $hotel->hasUploadedImage() ? $hotel->file_path : null;

        $attributes = [
            'hotel_name' => $draft['hotel_name'],
            'prefecture_id' => $draft['prefecture_id'],
        ];

        $newImage = null;
        if ($draft['tmp_image'] !== null) {
            $newImage = $this->images->publish($draft['tmp_image']);
            $attributes['file_path'] = $newImage;
        }

        $hotel->update($attributes);
        session()->forget(self::EDIT_DRAFT_KEY);

        // the old uploaded image is no longer used once it has been replaced
        if ($newImage !== null) {
            $this->images->delete($oldImage);
        }

        return redirect()
            ->route('adminHotelEditPage', ['hotel_id' => $hotel->hotel_id])
            ->with('hotel_edit_completed', $hotel->hotel_id);
    }

    private function getDraft(int $hotelId): ?array
    {
        $draft = session(self::EDIT_DRAFT_KEY);

        return is_array($draft) && $draft['hotel_id'] === $hotelId ? $draft : null;
    }

    private function discardDraft(bool $deleteImage = true): void
    {
        $draft = session()->pull(self::EDIT_DRAFT_KEY);
        if ($deleteImage && is_array($draft)) {
            $this->images->delete($draft['tmp_image']);
        }
    }
}
