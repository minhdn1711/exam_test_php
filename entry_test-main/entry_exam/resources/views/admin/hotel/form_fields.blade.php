{{-- shared input fields for hotel create / edit --}}
{{-- params: $prefectures, $hotelName, $prefectureId, $imageRequired, $currentImage (nullable), $keptImage (nullable) --}}
<div class="form-row @error('hotel_name') has-error @enderror">
    <label class="form-label" for="hotel_name">ホテル名<span class="required">必須</span></label>
    <input type="text" id="hotel_name" name="hotel_name" value="{{ $hotelName }}" maxlength="255" required>
    @error('hotel_name')
        <p class="error-message">{{ $message }}</p>
    @enderror
</div>

<div class="form-row @error('prefecture_id') has-error @enderror">
    <label class="form-label" for="prefecture_id">都道府県<span class="required">必須</span></label>
    <select id="prefecture_id" name="prefecture_id" required>
        <option value="">選択してください</option>
        @foreach ($prefectures as $prefecture)
            <option value="{{ $prefecture->prefecture_id }}" @selected((string) $prefectureId === (string) $prefecture->prefecture_id)>
                {{ $prefecture->prefecture_name }}
            </option>
        @endforeach
    </select>
    @error('prefecture_id')
        <p class="error-message">{{ $message }}</p>
    @enderror
</div>

@php
    // image shown when the form opens: kept from the previous submit > current hotel image
    $initialImage = $keptImage ?? $currentImage ?? null;
    $initialStatus = !empty($keptImage) ? '選択済みの画像' : (!empty($currentImage) ? '現在の画像' : '');
    $initialNote = !empty($keptImage) ? '前回選択した画像を使用します' : (!empty($currentImage) ? '変更しない場合は選択不要です' : '');
    $newStatus = empty($currentImage) ? '選択した画像' : '新しい画像';
@endphp
<div class="form-row @error('image') has-error @enderror">
    <span class="form-label" id="image-label">
        ホテル画像
        @if ($imageRequired)
            <span class="required">必須</span>
        @endif
    </span>
    @if (!empty($keptImage))
        <input type="hidden" name="keep_tmp_image" value="1" class="js-keep-image">
    @endif
    <label for="image" class="dropzone js-dropzone {{ $initialImage ? 'is-filled' : '' }}"
           data-initial-src="{{ $initialImage ? '/assets/img/' . $initialImage : '' }}"
           data-initial-status="{{ $initialStatus }}"
           data-initial-note="{{ $initialNote }}"
           data-new-status="{{ $newStatus }}">
        <input type="file" id="image" name="image" class="dropzone-input"
               accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
               aria-labelledby="image-label" aria-describedby="image-hint">

        <span class="dropzone-empty">
            <svg class="dropzone-icon" viewBox="0 0 48 48" aria-hidden="true">
                <rect x="6" y="9" width="36" height="30" rx="4" fill="none" stroke="currentColor" stroke-width="2.5"/>
                <circle cx="17" cy="19" r="3.5" fill="currentColor"/>
                <path d="M8 35l11-11 7 7 5-5 9 9" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
            </svg>
            <span class="dropzone-title">画像をここにドラッグ＆ドロップ</span>
            <span class="dropzone-sub">または <span class="dropzone-link">ファイルを選択</span></span>
        </span>

        <span class="dropzone-filled">
            <img class="dropzone-thumb js-dropzone-thumb" src="{{ $initialImage ? '/assets/img/' . $initialImage : '' }}" alt="">
            <span class="dropzone-meta">
                <span class="dropzone-status js-dropzone-status">{{ $initialStatus }}</span>
                <span class="dropzone-name js-dropzone-name">{{ $initialNote }}</span>
                <span class="btn btn-small dropzone-change" aria-hidden="true">画像を変更</span>
            </span>
        </span>
    </label>
    <p class="help" id="image-hint">JPG・PNG・GIF・WebP形式、2MBまで</p>
    <p class="error-message js-dropzone-error" hidden></p>
    @error('image')
        <p class="error-message">{{ $message }}</p>
    @enderror
</div>

<script>
    (function () {
        var zone = document.querySelector('.js-dropzone');
        var input = document.getElementById('image');
        var thumb = zone.querySelector('.js-dropzone-thumb');
        var status = zone.querySelector('.js-dropzone-status');
        var name = zone.querySelector('.js-dropzone-name');
        var error = document.querySelector('.js-dropzone-error');
        var keep = document.querySelector('.js-keep-image');
        var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        var maxBytes = 2 * 1024 * 1024;
        var objectUrl = null;

        function formatSize(bytes) {
            return bytes >= 1024 * 1024
                ? (bytes / 1024 / 1024).toFixed(1) + 'MB'
                : Math.max(1, Math.round(bytes / 1024)) + 'KB';
        }

        function showError(message) {
            error.textContent = message;
            error.hidden = !message;
            zone.classList.toggle('is-invalid', !!message);
        }

        function render() {
            var file = input.files && input.files[0];
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            // a newly selected file replaces the kept image; cancelling restores it
            if (keep) {
                keep.disabled = !!file;
            }

            if (!file) {
                showError('');
                thumb.src = zone.dataset.initialSrc;
                status.textContent = zone.dataset.initialStatus;
                name.textContent = zone.dataset.initialNote;
                zone.classList.toggle('is-filled', !!zone.dataset.initialSrc);
                return;
            }

            if (allowed.indexOf(file.type) === -1) {
                showError('JPG・PNG・GIF・WebP形式の画像を選択してください。');
            } else if (file.size > maxBytes) {
                showError('ファイルサイズが' + formatSize(file.size) + 'あります。2MB以下の画像を選択してください。');
            } else {
                showError('');
            }

            objectUrl = URL.createObjectURL(file);
            thumb.src = objectUrl;
            status.textContent = zone.dataset.newStatus;
            name.textContent = file.name + '（' + formatSize(file.size) + '）';
            zone.classList.add('is-filled');
        }

        input.addEventListener('change', render);

        ['dragenter', 'dragover'].forEach(function (type) {
            zone.addEventListener(type, function (event) {
                event.preventDefault();
                zone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (type) {
            zone.addEventListener(type, function (event) {
                event.preventDefault();
                zone.classList.remove('is-dragover');
            });
        });
        zone.addEventListener('drop', function (event) {
            var files = event.dataTransfer && event.dataTransfer.files;
            if (!files || !files.length) {
                return;
            }
            var transfer = new DataTransfer();
            transfer.items.add(files[0]);
            input.files = transfer.files;
            render();
        });
    })();
</script>
