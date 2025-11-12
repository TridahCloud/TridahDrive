<div 
    x-data="{ open: false }"
    class="help-widget-container"
    style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"
>
    <!-- Widget Button -->
    <button
        @click="open = !open"
        class="help-widget-button"
        style="
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #31d8b2 0%, #2bc4a0 100%);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(49, 216, 178, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        "
        onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(49, 216, 178, 0.6)'"
        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(49, 216, 178, 0.4)'"
        title="Help & Support"
    >
        <i class="fas fa-question-circle"></i>
    </button>

    <!-- Widget Popup -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 transform scale-95 translate-y-2"
        @click.away="open = false"
        x-cloak
        class="help-widget-popup"
        style="
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 320px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            padding: 24px;
            margin-bottom: 10px;
        "
    >
        <!-- Close Button -->
        <button
            @click="open = false"
            style="
                position: absolute;
                top: 12px;
                right: 12px;
                background: none;
                border: none;
                color: #6b7280;
                font-size: 20px;
                cursor: pointer;
                width: 28px;
                height: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.2s ease;
            "
            onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#374151'"
            onmouseout="this.style.backgroundColor='transparent'; this.style.color='#6b7280'"
        >
            <i class="fas fa-times"></i>
        </button>

        <!-- Content -->
        <div style="margin-top: 8px;">
            <h3 style="
                font-size: 20px;
                font-weight: 600;
                color: #111827;
                margin: 0 0 12px 0;
            ">
                Need Help?
            </h3>
            <p style="
                font-size: 14px;
                color: #6b7280;
                line-height: 1.6;
                margin: 0 0 20px 0;
            ">
                Having issues, feedback, or suggestions? Join our Discord community to get support and connect with other users!
            </p>
            <a
                href="https://discord.gg/xx6sJaxHKg"
                target="_blank"
                rel="noopener noreferrer"
                style="
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 20px;
                    background: linear-gradient(135deg, #5865F2 0%, #4752C4 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 500;
                    font-size: 14px;
                    transition: all 0.2s ease;
                    box-shadow: 0 2px 8px rgba(88, 101, 242, 0.3);
                "
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(88, 101, 242, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(88, 101, 242, 0.3)'"
            >
                <i class="fab fa-discord" style="font-size: 18px;"></i>
                Join Discord Community
            </a>
        </div>
    </div>
</div>

<style>
    /* Dark mode support */
    [data-theme="dark"] .help-widget-popup {
        background: #1f2937 !important;
        color: #f9fafb;
    }
    
    [data-theme="dark"] .help-widget-popup h3 {
        color: #f9fafb !important;
    }
    
    [data-theme="dark"] .help-widget-popup p {
        color: #d1d5db !important;
    }
    
    [data-theme="dark"] .help-widget-popup button {
        color: #d1d5db !important;
    }
    
    [data-theme="dark"] .help-widget-popup button:hover {
        background-color: #374151 !important;
        color: #f9fafb !important;
    }

    /* Responsive design */
    @media (max-width: 640px) {
        .help-widget-popup {
            width: calc(100vw - 40px) !important;
            right: -10px !important;
            max-width: 320px;
        }
    }
</style>

