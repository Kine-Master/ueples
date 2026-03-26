// frontend/login/script.js
document.addEventListener('DOMContentLoaded', () => {
    // Password Toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle abstract eye icon logic based on the user's classes
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // Auto-focus empty fields
    const form = document.getElementById('loginForm');
    if (form) {
        const inputs = form.querySelectorAll('input[required]');
        for (let i = 0; i < inputs.length; i++) {
            if (!inputs[i].value) {
                inputs[i].focus();
                break;
            }
        }
    }

    // Live Clock Update
    const clockElement = document.getElementById('liveClock');
    if (clockElement) {
        function updateClock() {
            const now = new Date();
            
            // Format Date: e.g. "Monday, Mar 26, 2026"
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
            const formattedDate = now.toLocaleDateString(undefined, dateOptions);
            
            // Format Time: e.g. "09:34 AM"
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            const formattedTime = now.toLocaleTimeString(undefined, timeOptions);
            
            clockElement.innerHTML = `<div>${formattedDate}</div><div style="font-weight:700; color:var(--text-main); font-size:1.1rem">${formattedTime}</div>`;
        }
        
        // Initial call
        updateClock();
        // Update every second
        setInterval(updateClock, 1000);
    }

    // Draggable & Bouncing Logo Physics
    const logo = document.getElementById('bouncingLogo');
    const container = document.getElementById('logoContainer');
    
    if (logo && container) {
        let isDragging = false;
        let pos = { x: 0, y: 0 };
        let vel = { x: 0, y: 0 };
        let lastMouse = { x: 0, y: 0 };
        let animationFrame;
        
        // Physics constants
        const friction = 0.98; // Friction when flying
        const bounceDamping = 0.7; // Speed kept after hitting a wall
        const dragFactor = 0.5; // Controls throw sensitivity
        
        // Center the logo initially
        const resetPosition = () => {
            const containerRect = container.getBoundingClientRect();
            const logoRect = logo.getBoundingClientRect();
            pos.x = (containerRect.width - logoRect.width) / 2;
            
            // Position it properly in the bottom half of the space
            // Assuming text takes up ~180px, place logo centered in the remaining space below the text
            pos.y = 180 + ((containerRect.height - 180) - logoRect.height) / 2;
            
            updateLogoStyle();
        };

        const updateLogoStyle = () => {
            logo.style.left = `${pos.x}px`;
            logo.style.top = `${pos.y}px`;
        };

        // Initialize position
        // Needs a tiny timeout to ensure CSS has applied widths
        setTimeout(resetPosition, 100);

        // Drag handlers
        logo.addEventListener('mousedown', (e) => {
            isDragging = true;
            cancelAnimationFrame(animationFrame);
            
            lastMouse = { x: e.clientX, y: e.clientY };
            vel = { x: 0, y: 0 };
            
            // Prevent default image drag behavior
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            
            const dx = e.clientX - lastMouse.x;
            const dy = e.clientY - lastMouse.y;
            
            pos.x += dx;
            pos.y += dy;
            
            updateLogoStyle();
            
            // Calculate velocity based on mouse movement
            vel.x = dx * dragFactor;
            vel.y = dy * dragFactor;
            
            lastMouse = { x: e.clientX, y: e.clientY };
        });

        document.addEventListener('mouseup', () => {
            if (!isDragging) return;
            isDragging = false;
            
            // Start physics loop when released
            physicsLoop();
        });

        const physicsLoop = () => {
            if (isDragging) return; // Stop if user grabs it again

            const containerRect = container.getBoundingClientRect();
            const logoRect = logo.getBoundingClientRect();

            // Apply velocity
            pos.x += vel.x;
            pos.y += vel.y;

            // Apply friction
            vel.x *= friction;
            vel.y *= friction;

            // Boundaries & Bouncing
            const maxX = containerRect.width - logoRect.width;
            const maxY = containerRect.height - logoRect.height;

            if (pos.x <= 0) {
                pos.x = 0;
                vel.x = Math.abs(vel.x) * bounceDamping;
            } else if (pos.x >= maxX) {
                pos.x = maxX;
                vel.x = -Math.abs(vel.x) * bounceDamping;
            }

            if (pos.y <= 0) {
                pos.y = 0;
                vel.y = Math.abs(vel.y) * bounceDamping;
            } else if (pos.y >= maxY) {
                pos.y = maxY;
                vel.y = -Math.abs(vel.y) * bounceDamping; // Add slightly more damping on floor bounces if preferred
            }

            updateLogoStyle();

            // Stop calculating if velocity is extremely low
            if (Math.abs(vel.x) > 0.1 || Math.abs(vel.y) > 0.1) {
                animationFrame = requestAnimationFrame(physicsLoop);
            }
        };

        // Window resize handler to keep it in bounds
        window.addEventListener('resize', () => {
            if (!isDragging) {
                const containerRect = container.getBoundingClientRect();
                const logoRect = logo.getBoundingClientRect();
                
                if (pos.x > containerRect.width - logoRect.width) pos.x = containerRect.width - logoRect.width;
                if (pos.y > containerRect.height - logoRect.height) pos.y = containerRect.height - logoRect.height;
                if (pos.x < 0) pos.x = 0;
                if (pos.y < 0) pos.y = 0;
                
                updateLogoStyle();
            }
        });
    }
});