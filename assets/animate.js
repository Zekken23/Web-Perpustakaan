document.addEventListener("DOMContentLoaded", function() {
    const mainContent = document.querySelector('body');
    mainContent.classList.add('fade-in');

    const deleteLinks = document.querySelectorAll('.btn-danger');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if(!confirm('Yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
});