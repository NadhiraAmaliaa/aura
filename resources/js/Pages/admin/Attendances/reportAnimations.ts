export const animationStyles = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes progressBarFill {
        from {
            width: 0% !important;
        }
    }

    .card-fade-in {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    .card-fade-in:nth-child(1) { animation-delay: 0.1s; }
    .card-fade-in:nth-child(2) { animation-delay: 0.2s; }
    .card-fade-in:nth-child(3) { animation-delay: 0.3s; }
    .card-fade-in:nth-child(4) { animation-delay: 0.4s; }
    .card-fade-in:nth-child(5) { animation-delay: 0.5s; }

    .icon-float {
        animation: float 3s ease-in-out infinite;
    }

    .progress-bar-fill {
        animation: progressBarFill 1s ease-out 0.3s forwards;
        width: 0% !important;
    }
`;
