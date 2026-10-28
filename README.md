# MMS Hostel — Luxury Hostel Booking Website

A responsive PHP + MySQL hotel/hostel booking starter project for local development with XAMPP.

## Features
- Luxury-inspired responsive home page
- Room search by dates, guests, and room type
- Room listing with filtering and availability checks
- Booking form and confirmation page
- Reservation down payment workflow (configurable percentage) with cash, GCash, bank transfer, and clearly labelled online-payment demo option
- Customer email confirmation after staff records and verifies enough down payment (requires SMTP configuration)
- Separate admin and staff login portals with role-based permissions
- Admin staff-account creation/deactivation
- Staff dashboard for reservations and guest check-in/check-out status
- Staff room management (add rooms, activate/hide rooms)
- Admin room management (add / activate / deactivate)
- Admin booking list and status updates
- Guest/reservation search and status management
- Manual payment ledger with staff submissions and admin verification
- Date-filtered operational reports and print view
- MySQL schema and sample rooms
- PDO prepared statements, password hashing, CSRF tokens, and basic input validation

## Requirements
- XAMPP with Apache and MySQL/MariaDB
- PHP 8.0+ recommended
- VS Code (optional)

## Installation (Windows + XAMPP)
1. Extract the `MMS_Hostel` folder into `C:\xampp\htdocs\`.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin`.
4. Create/import the database:
   - Click **Import**
   - Choose `database/mms_hostel.sql`
   - Click **Import**
5. Check `config/config.php`. The defaults are for a standard local XAMPP install:
   - host: `127.0.0.1`
   - database: `mms_hostel`
   - username: `root`
   - password: blank
6. Open `http://localhost/MMS_Hostel/`.
7. Admin sign-in: `http://localhost/MMS_Hostel/admin/login.php`
Reservations: `http://localhost/MMS_Hostel/admin/reservations.php`
Payments: `http://localhost/MMS_Hostel/admin/payments.php`
Reports: `http://localhost/MMS_Hostel/admin/reports.php`
Staff payment recording: `http://localhost/MMS_Hostel/staff/payments.php`

## Demo login credentials
Admin portal: `http://localhost/MMS_Hostel/admin/login.php`
- Email: `admin@mmshostel.local`
- Password: `ChangeMe123!`

Staff portal: `http://localhost/MMS_Hostel/staff/login.php`
- Email: `staff@mmshostel.local`
- Password: `StaffStart123!`

The admin dashboard has a **Manage staff** link for creating and deactivating staff accounts. Staff can manage reservation statuses and add/activate/hide rooms, but cannot access staff-account administration. Change demo passwords before deployment.

## Existing database upgrade
If you already have an MMS Hostel database and want to keep bookings, back it up and follow `UPGRADE_EXISTING_DATABASE.txt`. Copy the updated application files into your existing project, confirm `config/config.php` points to your current database, and run `database/upgrade_existing_database.php` once through localhost. This additive migration preserves existing rooms, bookings, and accounts; it adds missing role fields and the payment ledger only when needed. Delete the migration PHP file after it reports SUCCESS. Do NOT import `database/mms_hostel.sql` into an existing database: that fresh-install script drops and recreates project tables. Only use it for a brand-new empty project database.

## Reservation confirmation email
When staff or an admin changes a reservation status to `confirmed`, the site sends a confirmation email to the customer email entered on the booking. Configure SMTP in `config/config.php` first. If SMTP is not configured, the reservation status still updates and the staff/admin page displays a warning.

## Payment note
The online-payment option is deliberately a **demo/simulated checkout**, not a real payment gateway. Do not collect card numbers or payment credentials in this project. For real online payments, integrate a provider such as PayMongo or Xendit using its official hosted checkout/API, webhook verification, and test/live keys. Never store raw card details.

## Project structure
- `index.php` — landing page
- `rooms.php` — search and room listings
- `book.php` — booking form and booking creation
- `confirmation.php` — booking confirmation
- `payment.php` — demo online payment status
- `admin/` — admin login, room management, staff accounts, reservations, payment verification, and reports
- `staff/` — separate staff login, reservation dashboard, manual payment recording, and logout
- `includes/` — shared header/footer/functions
- `assets/css/style.css` — responsive styles
- `assets/js/app.js` — date validation and UI helpers
- `database/mms_hostel.sql` — database schema and sample data

## Important production checklist
This is a functional starter, not a production-certified booking platform. Before deployment:
- Replace demo credentials and configure secrets outside the public web root.
- Add HTTPS and secure cookie/session settings.
- Add rate limiting, audit logging, email/SMS notifications, backups, and monitoring.
- Integrate a real payment provider and verify payment status server-side via signed webhooks.
- Review privacy, consumer-protection, tax, and local business requirements.


## Email and down-payment setup
1. Back up your existing database in phpMyAdmin (Export > Quick > SQL).
2. Copy the updated files into `C:\xampp\htdocs\MMS_Hostel` without deleting your database.
3. Run `http://localhost/MMS_Hostel/database/upgrade_existing_database.php` once, then delete that PHP migration file after it prints SUCCESS.
4. Open `config/config.php` and configure SMTP. For Gmail, use `smtp.gmail.com`, port `587`, TLS, your Gmail address, and a Google App Password (not your regular Gmail password). Never share your app password.
5. In the same config file, replace the sample GCash number and bank details with your actual receiving details. Change `DOWN_PAYMENT_PERCENT` if your policy differs from 30%.
6. Test a booking using an email address you control. Staff should record the real down payment in Staff > Payments. Once verified payments reach the required percentage, the booking changes from `pending_payment` to `confirmed` and an email is attempted.
7. If the email does not arrive, check spam/junk and the PHP/Apache error log; verify SMTP credentials and outbound connection.

**Payment safety:** Cash, GCash, and bank transfer are manually verified by staff; this project does not automatically verify transactions with GCash or a bank. The `online_demo` option is only a simulation and does not collect money. To accept real online payments, integrate an actual gateway such as PayMongo or Xendit with server-side webhook verification.
