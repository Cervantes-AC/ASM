ASM/
├── includes/
│   ├── header.php           # Common page header (HTML <head>, nav menu, scripts)
│   ├── footer.php           # Common footer content (closing tags, footer scripts)
│   ├── navbar.php           # Navigation bar markup included on pages
│   └── auth_check.php       # Session and user authentication verification for protected pages
│
├── System/
│   ├── config/
│   │   └── db.php           # Database connection setup (PDO/MySQLi)
│   │
│   ├── auth/
│   │   ├── login.php        # Login form and authentication processing
│   │   ├── logout.php       # User logout, session destruction
│   │   └── register.php     # User registration by Admin with role assignment
│   │
│   ├── assets/
│   │   ├── list.php         # List all assets with filtering/search
│   │   ├── add.php          # Form and logic to add new asset records
│   │   ├── edit.php         # Edit existing asset details
│   │   └── delete.php       # Delete assets with confirmation
│   │
│   ├── borrow/
│   │   ├── request.php      # Submit borrow requests by members
│   │   ├── return.php       # Return asset form and status updates
│   │   └── overdue.php      # List overdue borrowings and notify users
│   │
│   ├── dashboard/
│   │   └── index.php        # Role-based dashboard landing page
│   │
│   ├── fines/
│   │   ├── add.php 
│   │   └── manage.php       # Admin page to manage fines, payments, and penalties
│   │
│   ├── logs/
│   │   └── view_logs.php    # Display system transaction logs (borrowing, returns, fines)
│   │
│   ├── users/
│   │   ├── profile.php      # User profile view and update page
│   │   ├── list.php         # List of all users (for Admin)
│   │   └── manage.php       # Admin user management (add, edit, disable)
│   │
│   ├── notifications/
│   │   └── index.php        # User notifications center (due dates, approvals, fines)
│   │
│   ├── about.php            #about page
│   └── index.php            # Main app entry (redirect to login or dashboard)
│
├── README.md                # Project overview, setup instructions, and file explanations
