<footer class="main-footer" style="border-top: 1px solid #f1f5f9; background: #fff; padding: 20px 25px; color: #64748b; font-size: 13px;">
    <div class="pull-right hidden-xs" style="color: #94a3b8;">
        <span style="letter-spacing: 0.5px; font-weight: 500;">
            <i class="fa fa-code-fork" style="margin-right: 5px;"></i>
            Mukahlera Build <span class="text-primary" style="font-weight: 700;">v{{ env('PORTAL_VERSION', '1.0.0') }}</span>
        </span>
    </div>

    <div class="footer-links">
        <strong style="color: #1e293b;">
            Copyright &copy; {{ date('Y') }}
            <a href="https://www.elias.co.zw" style="color: #3c8dbc; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#23527c'" onmouseout="this.style.color='#3c8dbc'">
                {{ config('app.name') }}
            </a>
        </strong>
        <span style="margin-left: 10px; padding-left: 10px; border-left: 1px solid #e2e8f0;">
            All rights reserved.
        </span>
    </div>
</footer>

<style>
    /* Ensures the footer sticks nicely even on short pages */
    .main-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    @media (max-width: 767px) {
        .main-footer {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        .footer-links span {
            border-left: none;
            display: block;
            margin-left: 0;
            padding-left: 0;
            margin-top: 5px;
        }
    }
</style>
