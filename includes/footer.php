</main> <!-- end of main content -->

    <footer>
        <p>&copy; <?= date("Y") ?> Central Mindanao University SSC</p>
    </footer>

</body>
</html>

<style>
    /* Footer positioning styles */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        padding-top: 70px; /* Account for fixed navbar */
        padding-left: 2rem;
        padding-right: 2rem;
        padding-bottom: 0; /* Remove bottom padding to let footer stretch */
    }

    main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
        width: 100%;
        min-height: calc(100vh - 140px); /* Ensure minimum height for content */
    }

    footer {
        background: #f1f1f1;
        text-align: center;
        padding: 1.5rem 0;
        margin-top: 2rem;
        margin-left: -2rem; /* Extend beyond body padding */
        margin-right: -2rem; /* Extend beyond body padding */
        border-top: 1px solid #ddd;
        width: calc(100% + 4rem); /* Full width accounting for body padding */
        box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
    }

    footer p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Mobile responsive adjustments */
    @media (max-width: 600px) {
        body {
            padding-top: 90px;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        main {
            min-height: calc(100vh - 160px);
        }
        
        footer {
            padding: 1rem 0;
            margin-left: -1rem;
            margin-right: -1rem;
            width: calc(100% + 2rem);
        }
        
        footer p {
            font-size: 0.8rem;
        }
    }
</style>