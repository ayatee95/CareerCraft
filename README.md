# CareerCraft

A web-based **portfolio builder**. Students, graduates and job seekers create an account, fill in their profile,
skills and projects, upload a resume (PDF) and get a public portfolio page they can share with recruiters.

Built with **PHP, MySQL, HTML, CSS and JavaScript** (no frameworks) as a learning project.

> **Status:** working locally on AppServ (Windows). It is a learning/portfolio project, **not** a production-ready
> product. See [Known limitations](#known-limitations) and [Testing status](#testing-status).

## Table of contents
1. [Overview and purpose](#overview-and-purpose)
2. [Features](#features)
3. [Technologies](#technologies)
4. [Project structure](#project-structure)
5. [Requirements](#requirements)
6. [Installation on Windows with AppServ](#installation-on-windows-with-appserv)
7. [Using the application](#using-the-application)
8. [Uploads and file handling](#uploads-and-file-handling)
9. [Security and privacy](#security-and-privacy)
10. [Known limitations](#known-limitations)
11. [Future improvements](#future-improvements)
12. [Testing status](#testing-status)
13. [Publishing to GitHub with GitHub Desktop](#publishing-to-github-with-github-desktop)
14. [Third-party assets and license](#third-party-assets-and-license)

## Overview and purpose
CareerCraft lets a person present their professional profile online in one place:
a short bio and job title, a list of skills, projects (with technologies and links) and a downloadable resume.
Each user manages only their own data, and a public page `portfolio.php?id=<user id>` shows the result.

The purpose of the project is to practise building a complete, secure PHP/MySQL application:
authentication, CRUD operations, file uploads, validation, and a responsive UI.

## Features
Everything listed here exists in the code of this repository.

- **Accounts:** registration (name, email, password; duplicate email check), login, logout.
- **Dashboard:** statistics (profile, skills, projects, resume), a progress indicator calculated from the user's data, quick links.
- **Profile:** job title, phone number (private), bio, profile picture upload (JPG, PNG, GIF, WEBP).
- **Skills:** add, edit and delete skills (duplicates are rejected).
- **Projects:** add, edit and delete projects with name, description, optional technologies and an optional http/https link.
- **Resume:** upload a PDF; uploading a new one replaces the old one (the old one is removed only after the new one is saved).
- **Settings:** change the display name and the password (the current password is required).
- **Public portfolio:** name, picture, job title, bio, skills, projects, technologies and resume download.
  Missing information is hidden; an unknown id shows a "Portfolio not found" page (HTTP 404).
- **Interface:** dark black-and-gold design, responsive layout with a slide-in menu on phones,
  keyboard-friendly focus styles, delete confirmation dialog.

## Technologies
- PHP (procedural, `mysqli` with prepared statements)
- MySQL / MariaDB (InnoDB, utf8mb4)
- HTML5 and CSS3 (custom design system with CSS variables, no framework)
- Vanilla JavaScript (small enhancements only: mobile menu, confirmation dialog, upload feedback)
- Inline SVG icons (no icon library) and the Poppins font bundled locally (see [Third-party assets](#third-party-assets-and-license))

## Project structure
```
index.php                 landing page
register.php, login.php   authentication
logout.php                ends the session (POST + CSRF token)
dashboard.php             overview, statistics, progress, quick links
profile.php               profile data and profile picture
skills.php                add / edit / delete skills
projects.php              add / edit / delete projects
resume.php                upload / replace the PDF resume
settings.php              display name and password
portfolio.php             public portfolio page (portfolio.php?id=...)

includes/
  config.example.php      example database settings (copy to config.php)
  config.php              YOUR database settings (you create it; never published)
  database.php            creates the database connection
  auth.php                protects private pages (login required)
  header.php, footer.php  page <head> and closing tags
  app_header.php          sidebar layout of the private pages
  app_footer.php
  functions.php           loads the helper files below
  security.php            escaping (XSS), sessions, CSRF, security headers
  validation.php          input cleaning and validation
  file_uploads.php        secure image and PDF uploads
  db_helpers.php          prepared-statement helpers
  helpers.php             alerts, flash messages, redirects, small display helpers
  icons.php               inline SVG icons

assets/
  css/style.css           the whole design system and responsive rules
  js/script.js            small UI enhancements
  fonts/                  Poppins (WOFF) and its license (OFL.txt)

database/
  schema.sql              creates an empty database for a fresh installation

uploads/                  profile pictures and resumes (empty in the repository)
```

## Requirements
- Windows with **AppServ** (Apache, PHP, MySQL/MariaDB, phpMyAdmin) - or any Apache + PHP + MySQL stack
- PHP 7.4 or newer recommended (the code uses no PHP 8-only syntax)
- PHP extensions: `mysqli` (with `mysqlnd`), `mbstring` (recommended), `fileinfo` (optional, extra PDF check)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `.htaccess` overrides enabled (`AllowOverride All`) so that the protections in `uploads/`, `includes/` and `database/` are active

## Installation on Windows with AppServ
1. **Install AppServ** and start Apache and MySQL. Note the MySQL `root` password you chose during the installation.
2. **Copy the project** into the web folder, for example `C:\AppServ\www\CareerCraft`
   (the folder name can be anything; all paths in the project are relative).
3. **Create the database.** Open `http://localhost/phpmyadmin`, log in, open the **Import** tab (at server level),
   choose `database/schema.sql` and click **Go**. This creates an empty database named `careercraft`
   with the tables `users`, `profiles`, `skills`, `projects` and `resume`.
   The script only uses `IF NOT EXISTS`; it never drops or changes existing data.
4. **Create your configuration file.** In the `includes` folder copy `config.example.php` and name the copy `config.php`.
   Open `config.php` and enter your MySQL user and password (and the database name if you changed it).
   Keep `'charset' => 'utf8mb4'` for a database created with `schema.sql`.
   `config.php` contains secrets and is excluded from Git - never publish it.
5. **Open the site:** `http://localhost/CareerCraft/` (use your folder name), click **Register** and create an account.

If the database connection fails, the page shows a generic message; the technical reason is written to the Apache/PHP error log.

## Using the application
1. **Register** and **log in**. You land on the dashboard.
2. **Profile:** add your job title, bio (and an optional private phone number) and a profile picture.
3. **Skills:** add the skills you want to show; use *Edit* or *Delete* next to a skill.
4. **Projects:** add a project with a description, optional technologies (comma-separated) and an optional link.
5. **Resume:** upload your PDF. Uploading again replaces it.
6. **Dashboard:** follow the progress checklist and use **View my portfolio** to open your public page.
7. Share the address `http://<your-site>/portfolio.php?id=<your id>` (you can see your id in that button's link).
8. **Settings:** change your display name or password. **Logout** is in the sidebar.

## Uploads and file handling
- Profile pictures go to `uploads/profiles/`, resumes to `uploads/resumes/`. Both folders are empty in the repository.
- **Pictures:** JPG, PNG, GIF or WEBP, up to 2 MB. The real image content is checked, not only the extension,
  and the server picks a random file name.
- **Resumes:** PDF only, up to 5 MB. The `%PDF-` signature is checked (and the MIME type when the `fileinfo` extension is available);
  the file name is sanitised and made unique.
- PHP's own limits (`upload_max_filesize` and `post_max_size` in `php.ini`) also apply and may be smaller; raise them if needed.
- `uploads/.htaccess` blocks execution of script files in the uploads folder. Uploaded files are never executed.
- If a file referenced in the database is missing on disk, the application hides it instead of showing a broken link.
- Uploads are **not** stored in Git (see `.gitignore`).

## Security and privacy
Implemented in the code (this is a list of measures, **not** a guarantee that the application is fully secure):

- SQL: prepared statements for all queries.
- Output: all user-supplied text is HTML-escaped; project links must be http/https.
- Forms: CSRF token on every form that changes data (including logout); delete actions use POST only.
- Passwords: `password_hash()` / `password_verify()`; minimum 8 characters; the current password is required to change it;
  the login error message does not reveal whether an email exists; the session id is regenerated at login.
- Sessions: cookie flagged `HttpOnly` and `SameSite=Lax` (and `Secure` over HTTPS); logout destroys the session.
- Ownership: every read, edit and delete of skills, projects and resumes is restricted to the logged-in user in the SQL itself
  (`... WHERE id = ? AND user_id = ?`), so changing an id in a URL or form does not reach another user's data.
- Uploads: validated by content, random/sanitised names, no script execution in `uploads/`.
- Headers: `Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`.
- Configuration: credentials live only in `includes/config.php`, which is ignored by Git; `includes/` and `database/` are not
  directly reachable from a browser (via `.htaccess`).
- Privacy: the public portfolio shows only name, picture, job title, bio, skills, projects and the resume.
  Email, phone number and password hash are never read by the public page.

**Please be aware:** the resume is public by design (it is offered for download on the portfolio), and its direct address
can be opened by anyone who knows it. Portfolio ids are sequential, so every portfolio is discoverable by trying numbers.

## Known limitations
- No password reset, no email verification and no "remember me".
- No protection against repeated login attempts (brute force) and no CAPTCHA.
- Every user's portfolio is public; there is no "private" switch. Portfolio addresses use sequential numeric ids.
- Uploaded resume files are public by their address (see above).
- No admin area, no account deletion, no pagination.
- English interface only; Poppins covers Latin text (other scripts use the system fallback font).
- The SemiBold (600) weight of Poppins is not bundled; headings that request 600 use the Bold file (see `assets/css/style.css`).
- `database/schema.sql` was written from the application code; compare it with your own database if you already have one.
- No automated tests. See [Testing status](#testing-status).
- For a real public website you must additionally use HTTPS, turn `display_errors` off in `php.ini`, and review the points above.

## Future improvements
- Password reset by email and email verification
- Login rate limiting
- A visibility option for portfolios and non-guessable public addresses (slugs)
- Automated tests (PHPUnit) and a CI workflow
- WOFF2 fonts and a full set of font weights
- Portfolio themes, drag-and-drop ordering of skills and projects, multi-language support

## Testing Status

The main application features have been manually tested locally using AppServ on Windows.

Completed tests:

- Registered two separate user accounts successfully.
- Added, edited, and deleted skills and projects.
- Verified that one user cannot edit another user's skills.
- Uploaded a profile picture and confirmed that it appeared correctly.
- Uploaded a valid PDF resume and tested rejection of an invalid PDF file.
- Changed the account password and tested login with the new password.
- Confirmed that the dashboard cannot be accessed after logout.
- Tested the public portfolio using an existing user ID and an unknown user ID.

All the tests listed above passed in the local environment. Automated tests have not yet been implemented.

This project is intended for learning and portfolio demonstration. Further security review and testing are recommended before deploying it as a public production service.

## Publishing to GitHub with GitHub Desktop
Before the first commit, make sure **none** of these are included: `includes/config.php`, anything inside `uploads/`
other than the three `index.html` files and `uploads/.htaccess`, `.sql` backups of your real database, `.zip` archives.

1. Open **GitHub Desktop** -> **File -> Add local repository...** and choose the project folder.
   If it says the folder is not a Git repository, click **create a repository**, keep the name, leave **Git ignore: None**
   and **License: None** (the folder already has a `.gitignore`), then **Create repository**.
2. In the **Changes** tab, read the list of files. You should see only source files, `assets/`, `database/schema.sql`,
   `README.md`, `.gitignore`, and the placeholder files in `uploads/`.
3. If you see `config.php`, a PDF, an image from `uploads/`, or a database backup: **stop**, do not commit, and fix the folder first.
4. Write a summary such as *Initial commit* and click **Commit to main**.
5. When you are ready, click **Publish repository**. Choose the name, decide private or public, and publish.
6. After publishing, open the repository on github.com and check that `includes/config.php` is not there.

## Third-party assets and license
- **Poppins** font, version 4.004, (c) 2020 The Poppins Project Authors (https://github.com/itfoundry/Poppins),
  licensed under the SIL Open Font License 1.1. The license text is in `assets/fonts/OFL.txt`.
  The font files were converted from TTF to WOFF without changing the glyphs.
- **Project license:** none has been chosen yet. Until a license file is added, the code is under the author's default copyright.
