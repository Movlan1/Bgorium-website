// FAQ accordion functionality
document.addEventListener('DOMContentLoaded', function () {
    const faqHeads = document.querySelectorAll('.faq__head');
    
    faqHeads.forEach(function (head) {
        head.addEventListener('click', function () {
            const faqItem = this.closest('.faq__item');
            const faqDesc = faqItem.querySelector('.faq__desc');
            const isActive = this.classList.contains('active');
            
            // Close all other FAQ items
            faqHeads.forEach(function (otherHead) {
                if (otherHead !== head) {
                    otherHead.classList.remove('active');
                    const otherItem = otherHead.closest('.faq__item');
                    const otherDesc = otherItem.querySelector('.faq__desc');
                    otherDesc.style.display = 'none';
                }
            });
            
            // Toggle current FAQ item
            if (isActive) {
                this.classList.remove('active');
                faqDesc.style.display = 'none';
            } else {
                this.classList.add('active');
                faqDesc.style.display = 'block';
            }
        });
    });
});

