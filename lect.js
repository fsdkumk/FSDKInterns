document.addEventListener("DOMContentLoaded", () => {
    console.log("Script initialized, DOM fully loaded");

    // Select required elements
    const modal = document.getElementById("addStudentModal");
    const addStudentBtn = document.getElementById("addStudentBtn");
    const closeBtn = document.querySelector("#addStudentModal .close");
    const addStudentForm = document.getElementById("addStudentForm");
    const tableBody = document.querySelector("#studentTable tbody");

    // Ensure modal is hidden by default
    modal.style.display = "none";

    // Open the modal when clicking the "Add Student" button
    addStudentBtn.addEventListener("click", () => {
        console.log("Opening modal...");
        modal.style.display = "flex";
    });

    // Close the modal when clicking the close button
    closeBtn.addEventListener("click", () => {
        console.log("Closing modal...");
        modal.style.display = "none";
    });

    // Close modal when clicking outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            console.log("Closing modal by clicking outside...");
            modal.style.display = "none";
        }
    });

    // Add student on form submit
    addStudentForm.addEventListener("submit", (event) => {
        event.preventDefault();
        console.log("Form submitted. Attempting to add student...");
        addStudent();
    });

    // Function to add a new student
    function addStudent() {
        const nameInput = document.getElementById("studentName").value.trim();
        const matrixInput = document.getElementById("studentMatrix").value.trim();

        // Check for empty input
        if (!nameInput || !matrixInput) {
            alert("Please fill out both Name and Matrix fields.");
            return;
        }

        console.log("Adding student with values:", { name: nameInput, matrix: matrixInput });

        // Create a new table row
        const newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td>${tableBody.rows.length + 1}</td>
            <td>${nameInput}</td>
            <td>${matrixInput}</td>
            <td><span class="task-indicator"></span></td>
        `;

        // Append the row to the table body
        tableBody.appendChild(newRow);

        // Add task indicator functionality
        const taskIndicator = newRow.querySelector(".task-indicator");
        taskIndicator.addEventListener("click", () => {
            taskIndicator.classList.toggle("done");
            console.log("Task status toggled for:", { name: nameInput, matrix: matrixInput });
        });

        // Reset the form and close the modal
        addStudentForm.reset();
        modal.style.display = "none";

        console.log("Student added successfully.");
    }

    // Sort table by Name column
    document.querySelector("button[onclick='sortTable()']").addEventListener("click", () => {
        sortTable();
    });

    function sortTable() {
        const rows = Array.from(tableBody.rows);

        rows.sort((a, b) => {
            const nameA = a.cells[1].textContent.trim().toLowerCase();
            const nameB = b.cells[1].textContent.trim().toLowerCase();

            return nameA.localeCompare(nameB);
        });

        // Re-append sorted rows
        rows.forEach((row) => tableBody.appendChild(row));

        // Update row numbers
        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });

        console.log("Table sorted by Name column.");
    }
});

// navbar
document.addEventListener("DOMContentLoaded", () => {
    console.log("Script initialized, DOM fully loaded");

    // Navbar scroll-hide functionality
    const navbar = document.querySelector(".navbar");
    let lastScrollTop = 0;

    window.addEventListener("scroll", () => {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (currentScrollTop > lastScrollTop) {
            // Scrolling down
            navbar.style.transform = "translateY(-100%)";
        } else {
            // Scrolling up
            navbar.style.transform = "translateY(0)";
        }

        lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop; // Prevent negative values
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const backToNavbar = document.getElementById("backToNavbar");

    // Show or hide the button based on scroll position
    window.addEventListener("scroll", () => {
        if (window.scrollY > 200) { // Show after scrolling 200px
            backToNavbar.classList.add("show");
        } else {
            backToNavbar.classList.remove("show");
        }
    });

    // Smooth scroll to the top when the button is clicked
    backToNavbar.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});

