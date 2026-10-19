## Entrance Exam for THK Holdings Vietnam in Hanoi

Welcome to THK Holdings Vietnam in Hanoi!!
Thank you for applying to us!

## About this exam

In this exam, you implement for these following fields using a Laravel application.
There are required and optional field to implement.

If you implement optional field, we consider your extra technical skills.

We will use Laravel Sail to build this exam environment.
Laravel Sail is a Laravel library that allows you to build an environment using virtualization container software called docker just by typing a command.
For more information about Laravel Sail, please refer to [the official Laravel documentation](https://readouble.com/laravel/11.x/en/sail.html).

### Application's information

[view](document/INFORMATION.md)

### Implementation fields

[view](document/IMPLEMENTATION_FIELD.md)

### Notice

- You write some comments as optional. But if you do, please write in Japanese or English.
- The design (CSS style) is up to you. Please use SCSS as possible.
- If there is unclear stated and specification, you implement the best for you.
- We will consider for the selection process from all of your implemented contents.
- Even if you couldn't complete all items, no problem. We will consider only your implemented scope. So please submit it by deadline.

### How to submit this exam

Please compress the `entry_test` folder into a zip file and submit it via email.
To reduce data size, please delete the `entry_exam/node_modules` folder and `entry_exam/vendor` folder along with all files under them before compressing.

### Any comments (optional)

If you have any comments about this exam, please write them down here.
```
Setup
  ./vendor/bin/sail up -d
  ./vendor/bin/sail artisan migrate:fresh --seed   # seeds prefectures, hotels and 200 sample bookings

Implemented (required)
  Hotel management screen: /admin/hotel/search (side menu "ホテル管理"). All hotels are listed
  (most recently updated first) until a search is made; the "ホテル追加" button opens the create screen.
  Notifications and the delete dialog use SweetAlert2 via realrashid/sweet-alert (@sweetAlert in the admin layout).

  1. Hotel create   : /admin/hotel/create
     - hotel name (required, max 255), prefecture (required, must exist), image (required, jpg/png/gif/webp, max 2MB)
     - images are saved to public/assets/img/hotel with a random file name
  2. Hotel edit     : 1. input -> 2. confirm -> 3. complete, using only the original routes
     - 1. input    : GET  /admin/hotel/edit?hotel_id=
     - 2. confirm  : POST /admin/hotel/edit (step=confirm) validates and shows the before/after comparison
     - 3. complete : POST /admin/hotel/edit (step=save) saves, then redirects to GET /admin/hotel/edit?hotel_id=
                     which shows the completion screen once (reloading it shows the input screen again)
     - the entered values are kept in the session between steps; "入力画面に戻る" restores them
     - a new image is first saved to public/assets/img/hotel/tmp and moved to public/assets/img/hotel only when
       the edit is saved; the old uploaded image is removed when it is replaced (seeded sample images are never deleted)
     - saving without going through the confirm screen is rejected
  3. Hotel delete   : SweetAlert confirm dialog, deleted only when "OK" is pressed
  4. Hotel search
     - empty search shows "何も入力されていません" in red directly below the form
     - partial match on hotel name (LIKE wildcards "%" and "_" are escaped)
     - prefecture select box, can be combined with the hotel name
     - Post/Redirect/Get: the form posts to POST /admin/hotel/search/result, which validates and redirects to
       GET /admin/hotel/search?hotel_name=&prefecture_id= so that the result can be reloaded / paginated (20 per page)

Implemented (optional)
  1. Responsive design (admin and user screens), admin styles moved from CSS to SCSS
  2. Booking search : /admin/booking/search ("予約情報検索" link in the side menu)
     - bookings table migration (FK to hotels with ON DELETE CASCADE) + BookingSeeder
     - search by customer name / contact (partial match), check-in date, check-out date
     - the ER diagram spells the columns "chekin_time" / "chekout_time"; I used the correct spelling
       "checkin_time" / "checkout_time"

Tests
  ./vendor/bin/sail artisan test   # feature tests for hotel search / create / edit / delete and booking search

Other fixes
  - Prefecture::hotel() relation now specifies the foreign key (prefecture_id)
  - hotel name in the search result links to the user hotel detail page
```
