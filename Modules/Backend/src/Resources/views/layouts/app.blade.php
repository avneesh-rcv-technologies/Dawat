<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png"
        href="{{ fn_get_setting('general.company.favicon') ? asset('storage/' . fn_get_setting('general.company.favicon')) : 'https://via.placeholder.com/60?text=+' }}">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>    
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js', 'Modules/Backend/src/Resources/assets/js/toast.js', 'Modules/Backend/src/Resources/assets/css/toast.css'])
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Alpine.js for notification bell -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @hook('backend:links.pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta')
    @yield('styles')
    @stack('styles')
    <style>
        /* Chrome, Edge, Safari */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #e5e7eb;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: #9ca3af #e5e7eb;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper .icon {
            position: absolute;
            top: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            pointer-events: none;
            color: #ABAFB1;
        }

        .password-wrapper .left-icon {
            left: 0.9rem;
        }

        .password-wrapper .right-icon {
            right: 0.9rem;
            cursor: pointer;
            pointer-events: auto;
        }
    </style>
    @hook('backend:styles.post')
    @hook('backend:links.post')
</head>

<body class="bg-[var(--color-bg)] text-[var(--color-text-gray)] font-sans">
    @hook('backend:body.pre')
    <div id="toast-container" class="toast-container"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('backend::layouts.sidebar')

        <!-- Main Area -->
        <div id="backend-main-layout" class="flex-1 flex flex-col overflow-hidden bg-topbar" style="min-width: 0;">
            <!-- Topbar -->
            @hook('backend:top_bar.pre')
            @include('backend::layouts.topbar')

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="bg-white min-h-screen">
                    <div data-testid="breadcrumb-links">
                        @yield('breadcrumbs')
                    </div>
                    <div class="@yield('content-class', 'space-y-6 px-6 md:px-28 py-6')">
                        @hook('backend:main.pre')
                        @yield('content')
                        @hook('backend:main.post')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <x-delete-modal />
    <x-status-modal />

    @hook('backend:scripts.pre')
    <script src="{{ asset('js/toast.js') }}"></script>
    
    <script>
        // Global fix for form-ajax button reference
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.form-ajax').forEach(function(form) {
                const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitButton && !form.button) {
                    form.button = submitButton;
                }
            });
        });
    </script>
    
    @yield('scripts')
    @stack('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function enhancePasswordInput(input) {
                if (input.dataset.enhanced === "true") return;
                input.dataset.enhanced = "true";

                // Create wrapper
                const wrapper = document.createElement("div");
                wrapper.style.position = "relative";
                wrapper.style.width = "100%";
                wrapper.style.display = "inline-block";

                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);

                // Add padding to input
                input.style.paddingLeft = "2.5rem";
                input.style.paddingRight = "2.5rem";

                // Left lock icon
                const leftIcon = document.createElement("span");
                leftIcon.style.position = "absolute";
                leftIcon.style.left = "0.9rem";
                leftIcon.style.color = "#ABAFB1";
                leftIcon.style.pointerEvents = "none";
                leftIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 17a2 2 0 100-4 2 2 0 000 4zm6-7V9a6 6 0 10-12 0v1m12 0H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2z" />
                    </svg>`;
                wrapper.appendChild(leftIcon);

                // Right eye icon
                const rightIcon = document.createElement("span");
                rightIcon.style.position = "absolute";
                rightIcon.style.right = "0.9rem";
                rightIcon.style.cursor = "pointer";
                rightIcon.style.color = "#ABAFB1";
                rightIcon.innerHTML = `
                    <svg width="24" height="24" fill="none" stroke="#9CA3AF" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3.5"/>
                    <path d="M20.188 10.9343C20.5762 11.4056 20.7703 11.6412 20.7703 12C20.7703 12.3588 20.5762 12.5944 20.188 13.0657C18.7679 14.7899 15.6357 18 12 18C8.36427 18 5.23206 14.7899 3.81197 13.0657C3.42381 12.5944 3.22973 12.3588 3.22973 12C3.22973 11.6412 3.42381 11.4056 3.81197 10.9343C5.23206 9.21014 8.36427 6 12 6C15.6357 6 18.7679 9.21014 20.188 10.9343Z"/>
                    </svg>`;
                wrapper.appendChild(rightIcon);

                // ⭐ FIX: Keep icons vertically centered no matter what happens around input
                function adjustIconPosition() {
                    const height = input.offsetHeight;
                    const mid = height / 2;
                    leftIcon.style.top = 11 + "px";
                    rightIcon.style.top = 9 + "px";
                }

                // Initial alignment
                setTimeout(adjustIconPosition, 10);

                // Re-align on events
                window.addEventListener("resize", adjustIconPosition);
                input.addEventListener("input", adjustIconPosition);
                input.addEventListener("focus", adjustIconPosition);
                input.addEventListener("blur", adjustIconPosition);

                // Observe parent for any DOM changes (like Laravel error message)
                const mutationObserver = new MutationObserver(adjustIconPosition);
                mutationObserver.observe(wrapper.parentNode, {
                    childList: true,
                    subtree: true
                });

                // Toggle visibility
                rightIcon.addEventListener("click", () => {
                    const isPassword = input.type === "password";
                    input.type = isPassword ? "text" : "password";

                    rightIcon.innerHTML = isPassword ?
                        `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.39453 10.5156C9.1445 10.9535 9 11.4596 9 12C9 13.6569 10.3431 15 12 15C12.5402 15 13.0455 14.8544 13.4834 14.6045L14.2109 15.332C13.5774 15.7532 12.8178 16 12 16C9.79086 16 8 14.2091 8 12C8 11.1821 8.24569 10.4217 8.66699 9.78809L9.39453 10.5156ZM12 8C14.2091 8 16 9.79086 16 12C16 12.2736 15.9722 12.5407 15.9199 12.7988L14.9961 11.875C14.9322 10.3173 13.6819 9.06629 12.124 9.00293L11.2002 8.0791C11.4586 8.02668 11.7262 8 12 8Z" fill="#9CA3AF"/>
                        <path d="M7.21777 8.33887C5.92029 9.30039 4.86765 10.4393 4.19824 11.252C3.77388 11.7672 3.72949 11.8565 3.72949 12C3.72949 12.1435 3.77388 12.2328 4.19824 12.748C4.8934 13.592 6.00033 14.79 7.36719 15.7734C8.73818 16.7598 10.3282 17.5 12 17.5C13.1985 17.5 14.3541 17.1179 15.418 16.5391L16.1523 17.2734C14.9184 17.987 13.5099 18.4999 12 18.5C10.0362 18.5 8.24221 17.6346 6.7832 16.585C5.32033 15.5325 4.15073 14.2639 3.42578 13.3838C3.07382 12.9565 2.72949 12.5741 2.72949 12C2.72949 11.4259 3.07382 11.0435 3.42578 10.6162C4.10362 9.79325 5.16942 8.63002 6.50098 7.62207L7.21777 8.33887ZM12 5.5C13.9638 5.50007 15.7578 6.36537 17.2168 7.41504C18.6797 8.46756 19.8493 9.73608 20.5742 10.6162C20.9262 11.0435 21.2705 11.4259 21.2705 12C21.2705 12.5741 20.9262 12.9565 20.5742 13.3838C20.0937 13.9672 19.416 14.7192 18.5889 15.4678L17.8809 14.7598C18.6764 14.0438 19.3335 13.3165 19.8018 12.748C20.226 12.2329 20.2705 12.1435 20.2705 12C20.2705 11.8565 20.226 11.7671 19.8018 11.252C19.1066 10.408 17.9997 9.20998 16.6328 8.22656C15.2619 7.24022 13.6717 6.50007 12 6.5C11.3058 6.5 10.6259 6.62876 9.96973 6.84863L9.18945 6.06836C10.0693 5.71793 11.0134 5.5 12 5.5Z" fill="#9CA3AF"/>
                        <path d="M5 2L21 18" stroke="#9CA3AF"/>
                        </svg>
                        ` :
                        `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="3.5" stroke="#9CA3AF"/>
                        <path d="M20.188 10.9343C20.5762 11.4056 20.7703 11.6412 20.7703 12C20.7703 12.3588 20.5762 12.5944 20.188 13.0657C18.7679 14.7899 15.6357 18 12 18C8.36427 18 5.23206 14.7899 3.81197 13.0657C3.42381 12.5944 3.22973 12.3588 3.22973 12C3.22973 11.6412 3.42381 11.4056 3.81197 10.9343C5.23206 9.21014 8.36427 6 12 6C15.6357 6 18.7679 9.21014 20.188 10.9343Z" stroke="#9CA3AF"/>
                        </svg>
                        `;
                    adjustIconPosition();
                });
            }

            // Enhance existing password fields
            document.querySelectorAll('input.input-field[type="password"]').forEach(enhancePasswordInput);

            // Observe for dynamically added inputs
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            if (node.matches && node.matches(
                                    'input.input-field[type="password"]')) {
                                enhancePasswordInput(node);
                            } else {
                                node.querySelectorAll?.(
                                    'input.input-field[type="password"]').forEach(
                                    enhancePasswordInput);
                            }
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>

    @hook('backend:scripts.post')

    @hook('backend:body.post')
</body>
</html>
