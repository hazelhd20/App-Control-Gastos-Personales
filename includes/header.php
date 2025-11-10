<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Control de Gastos'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .blue-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: white;
            color: #1e40af;
            border: 2px solid #1e40af;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #1e40af;
            color: white;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .alert-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-info {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        
        .toggle-password {
            cursor: pointer;
        }
        
        /* Unified Form Validation Styles */
        .form-error-summary {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            transition: opacity 0.3s ease;
        }
        
        .field-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2;
        }
        
        .field-error:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }
        
        .field-success {
            border-color: #10b981 !important;
            background-color: #f0fdf4;
        }
        
        .field-success:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }
        
        .field-warning {
            border-color: #f59e0b !important;
            background-color: #fffbeb;
        }
        
        .field-warning:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1) !important;
        }
        
        .field-error-message {
            display: flex;
            align-items: center;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc2626;
            animation: fadeIn 0.3s ease;
        }
        
        .field-warning-message {
            display: flex;
            align-items: center;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #d97706;
            animation: fadeIn 0.3s ease;
        }
        
        .field-success-message {
            display: flex;
            align-items: center;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #059669;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes field-shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Improved alert styles with icons */
        .alert-danger,
        .alert-success,
        .alert-warning,
        .alert-info {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: fadeIn 0.3s ease;
        }
        
        .alert-danger i,
        .alert-success i,
        .alert-warning i,
        .alert-info i {
            margin-top: 0.125rem;
            flex-shrink: 0;
        }
        
        .alert-danger ul,
        .alert-success ul,
        .alert-warning ul,
        .alert-info ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        /* Auto-hide alert animation */
        .alert-auto-hide {
            animation: slideInDown 0.3s ease, fadeOut 0.5s ease 4.5s;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeOut {
            to {
                opacity: 0;
            }
        }
        
        /* Improved spacing and alignment */
        .form-group {
            margin-bottom: 1rem;
        }
        
        /* Better card alignment */
        .card {
            display: flex;
            flex-direction: column;
        }
        
        /* Consistent button alignment */
        .btn-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        /* Better text alignment on mobile */
        @media (max-width: 640px) {
            .text-center-mobile {
                text-align: center;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
        
        /* Improved focus states */
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Better grid alignment */
        .grid-auto-fit {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
        
        /* Consistent spacing */
        .section-spacing {
            margin-bottom: 2rem;
        }
        
        @media (min-width: 640px) {
            .section-spacing {
                margin-bottom: 2.5rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">

