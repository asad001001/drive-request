# Drive Request — WordPress Test-Drive Booking Plugin

A WordPress plugin for car dealership networks. Customers book a test drive with a dealer in their city; each dealer manages their own requests from a front-end dashboard (accept, reject with reason, edit, rate, complete), and admins see everything.

## Features

- **Public booking form** – customer picks a city, the dealer list loads by AJAX, and a confirmation email is sent.
- **Dealer dashboards** – server-side DataTables for *pending*, *accepted/completed* and *rejected* requests with search, sorting and paging.
- **Request workflow** – Pending → Accepted → Completed, or Rejected (with reason). Customers are emailed on each step.
- **Dealer-created bookings** – dealers can log a walk-in test drive directly.
- **Dealer profiles** – extra user fields (city, province, phones, address) power the city → dealer lookup.
- **Role-aware** – a `dealer` role is added on activation; dealers only ever see and edit their own requests.

## Shortcodes

| Shortcode | Who | What |
|---|---|---|
| `[drive_request_form]` | Everyone | Test-drive booking form |
| `[getDealersByCity]` | Everyone | City → dealer picker with contact card |
| `[createRequestByDealer]` | Dealers | Book a test drive on behalf of a customer |
| `[getPendingRequestDT]` | Dealers / admins | Pending requests (accept / reject) |
| `[getAcceptedRequestDT]` | Dealers / admins | Accepted & completed (edit, view, rate) |
| `[getRejectedRequestDT]` | Dealers / admins | Rejected requests (re-accept) |

## Structure

```
drive-request/
├── drive-request.php            # Bootstrap: constants, includes, activation
├── includes/
│   ├── class-drive-request.php  # Install, assets, AJAX handlers
│   ├── helpers.php              # Nonce/role/ownership checks, sanitising, shared HTML
│   ├── datatables.php           # One shared server-side DataTables query builder
│   ├── modals.php               # Edit / view / reject modals
│   ├── shortcodes.php           # All shortcodes
│   └── user-fields.php          # Dealer profile fields
└── assets/
    ├── js/drive-request.js      # Forms, dashboard actions, modals
    ├── js/datatables-init.js    # Initialises every .dr-datatable
    ├── css/style.css
    └── img/ajax-loader.gif
```

## Security

- Every AJAX call is protected by a nonce (sent in the `X-DR-Nonce` header).
- Dashboard actions require a logged-in dealer or admin, and a dealer can only load or change **their own** rows (`dr_get_owned_record()`).
- Only whitelisted, sanitised columns are written to the database — no raw `$_POST` inserts.
- All SQL goes through `$wpdb->prepare()`; sort column and direction are whitelisted.
- All output is escaped (`esc_html`, `esc_attr`).

## Installation

1. Upload the folder to `wp-content/plugins/` and activate **Drive Request**.
2. Create dealer users (role **Dealer**) and fill in their *Dealer details* (city is required for the city lookup).
3. Add the shortcodes to your pages.

## Credits

Originally written by Nand Lal Bheel. Refactored and secured by **Asad Ali** (v3.0).

## License

GPL-2.0-or-later
