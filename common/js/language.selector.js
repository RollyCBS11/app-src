document.addEventListener('DOMContentLoaded', function() {
    // Select all the language switchers
    var switchers = document.querySelectorAll('.language-switcher');

    // Add click listener to each switcher for toggling the class
    Array.prototype.forEach.call(switchers, function(switcher) {
        switcher.addEventListener('click', function(event) {
            // Prevent the dropdown from closing if clicking inside
            event.stopPropagation();
            
            // Toggle active class (classList is supported in IE10+)
            if (this.classList.contains("active")) {
                this.classList.remove("active");
            } else {
                this.classList.add("active");
            }
        });
    });

    // Add a global click listener to the document to close dropdowns
    document.addEventListener('click', function(event) {
        Array.prototype.forEach.call(switchers, function(switcher) {
            // If clicking outside, remove the 'active' class
            if (!switcher.contains(event.target)) {
                switcher.classList.remove('active');
            }
        });
    });

    // Handle option clicks separately
    var options = document.querySelectorAll('.language-switcher .option');
    Array.prototype.forEach.call(options, function(option) {
        option.addEventListener('click', function(event) {
            // Prevent the toggle listener above from firing
            event.stopPropagation();
                        
            // Get the URL and redirect
            var lang = option.getAttribute('data-value');
            if (event.isTrusted) {              
                const searchParams = new URLSearchParams(window.location.search);
                searchParams.set('lang', lang);
                window.location.search = searchParams.toString(); 
            }
        });
    });
});

