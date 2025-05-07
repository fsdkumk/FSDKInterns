document.addEventListener("DOMContentLoaded", () => {
    console.log("Script initialized, DOM fully loaded");

    // Get modal and trigger
    const modalTrigger = document.getElementById("openModal");
    const submissionModalElement = document.getElementById("submissionModal");

    // Check modal trigger and element existence
    if (modalTrigger && submissionModalElement) {
        // Initialize Bootstrap modal instance
        const submissionModal = new bootstrap.Modal(submissionModalElement);

        // Open modal only when the trigger is clicked
        modalTrigger.addEventListener("click", (event) => {
            event.preventDefault(); // Prevent default behavior
            submissionModal.show(); // Show the modal
        });
    }

    // Add Submission Button and File Upload Form functionality
    const addSubmissionBtn = document.getElementById("addSubmissionBtn");
    const submissionForm = document.getElementById("submissionForm");

    if (addSubmissionBtn && submissionForm) {
        addSubmissionBtn.addEventListener("click", () => {
            // Show the file upload form and hide the "Add Submission" button
            submissionForm.style.display = "block";
            addSubmissionBtn.style.display = "none";
        });

        submissionForm.addEventListener("submit", (event) => {
            event.preventDefault(); // Prevent default form submission
            const formData = new FormData(submissionForm);

            // Example: AJAX request to handle file upload
            fetch("upload_submission.php", {
                method: "POST",
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    alert(data.message || "File uploaded successfully!");
                    submissionForm.reset(); // Reset the form after submission
                    submissionForm.style.display = "none"; // Hide the form
                    addSubmissionBtn.style.display = "block"; // Show the "Add Submission" button again

                    // Determine the section to display the Done button
                    const section = data.section || "BeforeLI"; // Replace 'BeforeLI' with logic to dynamically determine the section
                    const doneButtonContainer = document.getElementById(`doneButtonContainer${section}`);
                    if (doneButtonContainer) {
                        doneButtonContainer.style.display = "block";
                    }
                })
                .catch((error) => {
                    console.error("Error uploading file:", error);
                    alert("There was an error uploading your file.");
                });
        });
    }

    // Handle Progress and Final Report Modals (Optional for other sections)
    const progressModalElement = document.getElementById("progressModal");
    const finalReportModalElement = document.getElementById("finalReportModal");

    if (progressModalElement) {
        const progressModal = new bootstrap.Modal(progressModalElement);
        const progressForm = progressModalElement.querySelector("form");

        if (progressForm) {
            progressForm.addEventListener("submit", (event) => {
                event.preventDefault();
                alert("Progress report submitted successfully!");
                progressModal.hide();
            });
        }
    }

    if (finalReportModalElement) {
        const finalReportModal = new bootstrap.Modal(finalReportModalElement);
        const finalForm = finalReportModalElement.querySelector("form");

        if (finalForm) {
            finalForm.addEventListener("submit", (event) => {
                event.preventDefault();
                alert("Final report submitted successfully!");
                finalReportModal.hide();
            });
        }
    }

    // Handle "Done" Buttons
    const doneButtons = [
        { id: "doneButtonBeforeLI", section: "Before LI" },
        { id: "doneButtonDuringLI", section: "During LI" },
        { id: "doneButtonAfterLI", section: "After LI" },
    ];

    doneButtons.forEach((btn) => {
        const doneButton = document.getElementById(btn.id);
        if (doneButton) {
            doneButton.addEventListener("click", () => {
                alert(`Submission for ${btn.section} marked as done!`);
            });
        }
    });

    // Optional: Debugging for modal events (show/hide)
    if (submissionModalElement) {
        submissionModalElement.addEventListener("show.bs.modal", () => {
            console.log("Submission Modal is about to be shown.");
        });

        submissionModalElement.addEventListener("hide.bs.modal", () => {
            console.log("Submission Modal is about to be hidden.");
        });
    }
});

