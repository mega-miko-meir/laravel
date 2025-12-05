// resources/js/search.js
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search");
    const employeeItems = document.querySelectorAll(".employee-item");

    searchInput.addEventListener("input", function () {
        const searchValue = searchInput.value.toLowerCase();

        employeeItems.forEach(function (item) {
            const employeeName = item
                .querySelector(".employee-name")
                .textContent.toLowerCase();
            if (employeeName.includes(searchValue)) {
                item.style.display = "block";
            } else {
                item.style.display = "none";
            }
        });
    });
});
