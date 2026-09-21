var layer = document.getElementById("address-suggest");

function closePostcode() {
    layer.style.display = "none";
}

document.getElementById("fields_zip").addEventListener("focus", () => {
    
    layer.style.display = "block";  
    var postcode =new daum.Postcode({
        oncomplete(data) {
           // document.getElementById("address").value = data.address;
            closePostcode()
        },
        onclose() {
            closePostcode();
        }
    });
    postcode.embed(layer);

    // Wait until the iframe is inserted and loaded
    const observer = new MutationObserver(() => {
        const iframe = layer.querySelector("iframe");

        if (iframe) {
            iframe.addEventListener("load", () => {
                const loading = layer.querySelector(".loading");
                if (loading) loading.remove();
            });

            observer.disconnect();
        }
    });

    observer.observe(layer, {
        childList: true
    });

});

document.addEventListener("click", (e) => {
    var input = document.getElementById("fields_zip");
    if (
        !layer.contains(e.target) &&
        e.target !== input
    ) {
        closePostcode();
    }
});

function resizePostcode() {
    var layer = document.getElementById("address-suggest");

    if (window.innerWidth < 768) {
        // Mobile
        layer.style.width = "100%";
        layer.style.height = "auto";
    } else {
        // Desktop
        layer.style.width = "100%";
        layer.style.height = "auto";
    }
}

window.addEventListener("resize", resizePostcode);
resizePostcode();