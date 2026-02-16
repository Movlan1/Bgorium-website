document.addEventListener("DOMContentLoaded", function () {
    const url = window.location.href.toLowerCase(); 

    const items = document.querySelectorAll("[data-u-chapter-id]");

    items.forEach(item => {
        const id = item.getAttribute("data-u-chapter-id")?.toLowerCase().trim();
        if (!id) return;

        // URL həmin sözü ehtiva edirsə
        if (url.includes(id)) {

            // ən yaxın u-chapters konteynerini tap
            const parent = item.closest(".u-chapters__item");

            if (parent) {
                parent.classList.add("u-chapters__item_active");
            }

            // İstəsən li-yə də active verim
            item.classList.add("chapter-li-active");
        }
    });
});
