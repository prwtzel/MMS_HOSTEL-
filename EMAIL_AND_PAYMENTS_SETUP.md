# MMS Hostel Email & Down-Payment Setup

## 1) Database migration
Back up `mms_hostel` first. Copy the updated project files into `C:\xampp\htdocs\MMS_Hostel`. Start Apache/MySQL, visit `http://localhost/MMS_Hostel/database/upgrade_existing_database.php` once, then delete that migration PHP file after SUCCESS. It does not drop existing tables or rows.

## 2) Configure email
Edit `config/config.php`: set `SMTP_USERNAME`, `SMTP_PASSWORD`, and `MAIL_FROM_ADDRESS`. For Gmail, enable 2-Step Verification and create a Google App Password; use that app password, not your normal Gmail password. Default host/port/security are `smtp.gmail.com`, `587`, and `tls`. Test with a real mailbox you control.

## 3) Configure deposit and payment instructions
`DOWN_PAYMENT_PERCENT` defaults to 30. Replace the sample `GCASH_NUMBER`, `BANK_NAME`, `BANK_ACCOUNT_NAME`, and `BANK_ACCOUNT_NUMBER` with the hostel's actual payment details. Do not accept payments while placeholders remain.

## 4) Workflow
Customer chooses Cash, GCash, Bank Transfer, or Online Payment (Demo). The booking remains `pending_payment`. Staff records the payment only after actually receiving/checking it. When verified payments reach the configured deposit, the system changes status to `confirmed` and attempts to email the customer the booking reference, dates, payment received, and remaining balance.

## 5) Important limits
GCash/bank transfer are manually verified; this app does not check their systems automatically. The online option is a simulation and does not collect money. Real online payments require an account with a provider (e.g. PayMongo/Xendit), API keys, hosted checkout, and signed webhook verification.


## Reservation confirmation email

When staff or an admin changes a reservation from a non-confirmed status to `confirmed`, the system emails the guest address entered during booking. The status update still succeeds if SMTP is not configured, but the page warns that the email was not sent. Configure the SMTP constants in `config/config.php`, then test with an email address you can access. The system does not send another confirmation email when a booking already marked confirmed is saved again.
