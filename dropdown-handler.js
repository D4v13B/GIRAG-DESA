function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    let activeDropdown = null;

    function positionDropdown(button, menu) {
        const buttonRect = button.getBoundingClientRect();
        const tableContainer = button.closest('.table-responsive');
        const tableRect = tableContainer.getBoundingClientRect();
        
        // Calculate available space
        const spaceBelow = window.innerHeight - buttonRect.bottom;
        const spaceAbove = buttonRect.top;
        
        // Position horizontally
        let left = buttonRect.left - tableContainer.scrollLeft;
        const menuWidth = 160; // Default dropdown width
        
        // Adjust horizontal position if would overflow
        if (left + menuWidth > tableRect.right) {
            left = buttonRect.right - menuWidth - tableContainer.scrollLeft;
        }
        
        // Position vertically
        let top;
        if (spaceBelow >= 200) { // If enough space below
            top = buttonRect.bottom;
            menu.classList.remove('dropdown-menu-up');
        } else if (spaceAbove >= 200) { // If enough space above
            top = buttonRect.top - menu.offsetHeight;
            menu.classList.add('dropdown-menu-up');
        } else {
            // If neither above nor below has enough space, show below anyway
            top = buttonRect.bottom;
            menu.classList.remove('dropdown-menu-up');
        }
        
        // Apply positions
        menu.style.position = 'fixed';
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
        activeDropdown = null;
    }

    dropdowns.forEach(dropdown => {
        const button = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const isCurrentlyOpen = menu.style.display === 'block';

            closeAllDropdowns();

            if (!isCurrentlyOpen) {
                menu.style.display = 'block';
                positionDropdown(button, menu);
                activeDropdown = menu;
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            closeAllDropdowns();
        }
    });

    // Reposition dropdown on scroll
    document.querySelector('.table-responsive').addEventListener('scroll', () => {
        if (activeDropdown) {
            const button = activeDropdown.previousElementSibling;
            positionDropdown(button, activeDropdown);
        }
    });

    // Close dropdowns on window resize
    window.addEventListener('resize', closeAllDropdowns);
}

// Initialize dropdowns after dynamic content load