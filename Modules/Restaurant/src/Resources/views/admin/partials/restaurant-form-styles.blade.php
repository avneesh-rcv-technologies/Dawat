<style>
    /* Form Elements */
    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .input-field {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease-in-out;
        background-color: #ffffff;
    }

    .input-field:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .input-field.input-error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }


    /* Tab Styles */
    .tab-btn {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background: none;
        border: none;
        border-radius: 0;
    }

    .tab-btn:hover {
        color: #374151;
        background-color: #f9fafb;
    }

    .tab-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        background: none;
    }

    .tab-btn.tab-active {
        color: #FF6210;
        border-bottom-color: #FF6210;
        background-color: #eff6ff;
    }

    .tab-content {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .grid.md\\:grid-cols-2 {
            grid-template-columns: 1fr;
        }

        .tab-btn {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }

        .tab-btn svg {
            width: 1rem;
            height: 1rem;
        }
    }

    /* ===== Section Card Layout ===== */
    .section-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    .section-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .section-card-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .section-card-desc {
        font-size: 0.78rem;
        color: #6b7280;
        margin: 0;
    }

    .section-card-body {
        padding: 1.5rem;
    }

    .section-card-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-top: 1.25rem;
        margin-top: 1.5rem;
        border-top: 1px solid #f3f4f6;
    }

    /* Step badge — uses existing brand color */
    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background-color: #FF6210;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* Info badge — subtle gray pill */
    .info-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.7rem;
        font-weight: 500;
        color: #6b7280;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        padding: 0.2rem 0.6rem;
        white-space: nowrap;
    }

    /* Working hours row */
    .hours-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
    }

    .hours-row:hover {
        background: #f3f4f6;
    }

    .day-name-pill {
        display: inline-block;
        width: 6rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
    }

    .hours-row-controls {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .hours-open-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        min-width: 4.5rem;
    }
</style>
