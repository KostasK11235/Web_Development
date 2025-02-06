document.addEventListener("DOMContentLoaded", function () {
  // Fetch theses only if on professor_theses.html
  if(document.querySelector(".announcements-container")) {
    fetchAnnouncements();
  }
  else if (document.querySelector(".thesis-container")) {
    fetchTheses("supervised","thesisList","");
  }
  else if(document.querySelector(".theses-member-container")) {
    fetchTheses("participated","participatedThesesList","");
  }
  else if(document.querySelector(".invitations-container")){
    fetchInvitations();
  }
  else if (document.querySelector(".assign-thesis-container")) {
    fetchTheses("supervised","thesisList","Υπό Ανάθεση")
  }
  else if(document.querySelector(".manage-theses-container")) {
    fetchTheses("participated","participatedThesesList","");
  }
});

/* Fetch announcements from fetch_announcements.php */
function fetchAnnouncements(){
  const announcementsList = document.getElementById("announcements");
	
	// Fetch data from server
	fetch("fetch_announcements.php")
		.then((response) => {
		  if(!response.ok) {
			  throw new Error('HTTP error! status: ${response.status}');
		  }
		  return response.json();
		  })
		.then((data) => {
			if(data.error) {
				announcementsList.innerHTML = '<p>${data.error}</p>';
				return;
			}
			
			if(data.length === 0) {
				announcementsList.innerHTML = "<p>No announcements found.</p>";
				return;
			}
			
			let html = "";
			data.forEach((announcement) => {
				html += `
					<div class="announcement-item">
					  <h2>Τίτλος: ${announcement.ann_title}</h2>
          	<p>${announcement.ann_content.substring(0, 256)}...</p>
          	<p><small>${announcement.ann_date}</small></p>
        	</div>
        `;
      });
        		
      announcementsList.innerHTML = html;
      })
      .catch((error)=>{
        console.error("Error fetching announcements:", error);
        announcementsList.innerHTML = "<p>Failed to load announcements. Please try again later.</p>";
      });
}

/* Fetch theses data from fetch_theses.php */
function fetchTheses(position, listId, status) {
  const thesisList = document.getElementById(listId);
  let detailsPage = document.querySelector(".manage-theses-container") ? "manage_thesis_details.html" : "show_thesis_details.html";

  // Fetch data from the server
  fetch(`fetch_theses.php?queryType=${position}&status=${status}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.error) {
        thesisList.innerHTML = `<p>${data.error}</p>`;
        return;
      }

      if (data.length === 0) {
        thesisList.innerHTML = "<p>No theses found for this professor.</p>";
        return;
      }

      let html = "";
      data.forEach((thesis) => {
        if (status === "")
        {
        html += `
          <div class="thesis-item">
            <h2>
              <a href="${detailsPage}?title=${encodeURIComponent(thesis.th_title)}&thesis_id=${thesis.th_id}" class="thesis-link">
                <strong>Θέμα:</strong> ${thesis.th_title}
              </a>
            </h2>
            <p><strong>Περιγραφή:</strong>${thesis.th_description}</p>
            <p><strong>Κατάσταση:</strong> ${thesis.th_status}</p>
            ${position === "participated" && thesis.prof_full_name ? `<p><strong>Επιβλέπων:</strong> ${thesis.prof_full_name}</p>` : ""}
          </div>
        `;
        }
        else {
          html += `
          <div class="assign-thesis-item">
            <h2>
              <a href="show_thesis_details.html?title=${encodeURIComponent(thesis.th_title)}" class="thesis-link">
                <strong>Θέμα:</strong>${thesis.th_title}
              </a>
            </h2>
            <p><strong>Περιγραφή:</strong>${thesis.th_description}</p>
            <p><strong>Κατάσταση:</strong> ${thesis.th_status}</p>
            <div class="assign-buttons-container">
              <!-- Row for search field and search button -->
              <div class="search-buttons">
                <input type="text" class="search-field" placeholder="AM or Full Name" maxlength="50">
                <button class="search-button">Αναζήτηση</button>
              </div>

              <!-- Row for assign and cancel buttons -->
              <div class="assign-buttons">
                <button class="assign-button" data-thesis-id="${thesis.th_id}">Επιβεβαίωση</button>
                <button class="cancel-button">Ακύρωση</button>
              </div>
            </div>
          </div>
        `;
        }
      });

      thesisList.innerHTML = html;

      // Add event listeners for search/confirm/cancel buttons
      document.querySelectorAll(".search-button").forEach((button) => {
        button.addEventListener("click", function () {
          const inputField = document.querySelector(".search-field");
          const searchValue = inputField.value.trim();

          if (searchValue) {
            searchStudentAction(searchValue);
          } else {
            alert("Please enter a student AM or name.");
          }
        });
      });

      document.querySelectorAll(".assign-button").forEach((button) => {
        button.addEventListener("click", function () {
          const thesisId = button.dataset.thesisId;
          // Find the parent container of the button
          const parentContainer = button.closest(".assign-thesis-item");
          const inputField = document.querySelector(".search-field");
          const studentName = inputField.value.trim();

          if (studentName) {
            handleAssignmentAction(thesisId, studentName);
          } else {
            alert("No student selected for assignment.");
          }
        });
      });

      document.querySelectorAll(".cancel-button").forEach((button) => {
        button.addEventListener("click", function () {
          const inputField = document.querySelector(".search-field");
          inputField.value = ""; // Clear the input field
        });
      });
    })

    .catch((error) => {
      console.error("Error fetching theses:", error);
      thesisList.innerHTML = "<p>Failed to load theses. Please try again later.</p>";
    });
}

/* Search for the student with the given AM or name on assign_thesis.html */
// Fetch and display student name
function searchStudentAction(searchValue) {
  fetch("search_student.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `student=${encodeURIComponent(searchValue)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      const inputField = document.querySelector(".search-field"); // Ensure this points to the correct field

      if (data.error) {
        alert(`Error: ${data.error}`);
      } else if (data.length > 0 && data[0].std_full_name) {
        inputField.value = data[0].std_full_name; // Display the student's name in the field
      } else {
        const inputField = document.querySelector(".search-field");
        inputField.value = ""; // Clear the input field
        alert("No matching student found.");
      }
    })
    .catch((error) => {
      console.error("Error searching student:", error);
      alert("An error occurred. Please try again.");
    });
}

/* Assign the thesis to the selected student */
function handleAssignmentAction(thesisId, studentName) {
  fetch("handle_assignment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `thesisId=${encodeURIComponent(thesisId)}&studentName=${encodeURIComponent(studentName)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        alert(`Error: ${data.error}`);
      } else if (data.success) {
        alert(data.success);
        location.reload(); // Reload the page to update the list
      }
    })
    .catch((error) => {
      console.error("Error assigning thesis:", error);
      alert("An error occurred. Please try again.");
    });
}

/* Fetch theses data based on the Search clauses */
document.addEventListener("DOMContentLoaded", function () {
  const participatedThesesList = document.getElementById("participatedThesesList");
  const confirmFiltersBtn = document.getElementById("confirmFiltersBtn");

  if (confirmFiltersBtn) {
    confirmFiltersBtn.addEventListener("click", function () {
      // Get the selected values from the dropdown filters
      const statusFilter = document.getElementById("statusFilter").value || "";
      const roleFilter = document.getElementById("roleFilter").value || "";
      const queryType = "participated";

      // Send the selected values to the PHP script
      fetch(`fetch_theses.php?queryType=${queryType}&status=${statusFilter}&role=${roleFilter}`)
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          if (data.error) {
            participatedThesesList.innerHTML = `<p>${data.error}</p>`;
            return;
          }

          if (data.length === 0) {
            participatedThesesList.innerHTML = "<p>No theses found matching the filters.</p>";
            return;
          }

          let html = "";
          data.forEach((thesis) => {
            html += `
              <div class="thesis-item">
                <h2><strong>Θέμα:</strong> ${thesis.th_title}</h2>
                <p><strong>Περιγραφή:</strong> ${thesis.th_description}</p>
                <p><strong>Κατάσταση:</strong> ${thesis.th_status}</p>
                <p><strong>Επιβλέπων:</strong> ${thesis.prof_full_name}</p>
              </div>
            `;
          });

          participatedThesesList.innerHTML = html;
        })
        .catch((error) => {
          console.error("Error fetching theses:", error);
          participatedThesesList.innerHTML = "<p>Failed to load theses. Please try again later.</p>";
        });
    });
  }
});

// Fetch invitations data from fetch_invitations.php
function fetchInvitations() {
  const invitationsContainer = document.getElementById("invitationsList");

  // Fetch invitations from the server
  fetch("fetch_invitations.php")
      .then((response) => {
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
      })
      .then((data) => {
          if (data.error) {
              invitationsContainer.innerHTML = `<p>${data.error}</p>`;
              return;
          }

          if (data.length === 0) {
              invitationsContainer.innerHTML = "<p>No invitations found.</p>";
              return;
          }

          // Generate HTML for invitations
          let html = "";
          data.forEach((invitation) => {
              html += `
                  <div class="invitation-item">
                      <h2>${invitation.th_title}</h2>
                      <p><strong>Περιγραφή:</strong> ${invitation.th_description}</p>
                      <p><strong>Επιβλέπων:</strong> ${invitation.th_supervisor}</p>
                      <div class="invitation-buttons">
                          <button class="accept-button" data-thesis-id="${invitation.th_id}">Αποδοχή</button>
                          <button class="reject-button" data-thesis-id="${invitation.th_id}">Απόρριψη</button>
                      </div>
                  </div>
              `;
          });

          invitationsContainer.innerHTML = html;

          // Add event listeners for accept/reject buttons
          document.querySelectorAll(".accept-button").forEach((button) => {
              button.addEventListener("click", function () {
                  handleInvitationAction(button.dataset.thesisId, "Accept");
              });
          });

          document.querySelectorAll(".reject-button").forEach((button) => {
              button.addEventListener("click", function () {
                  handleInvitationAction(button.dataset.thesisId, "Decline");
              });
          });
      })
      .catch((error) => {
          console.error("Error fetching invitations:", error);
          invitationsContainer.innerHTML = "<p>Failed to load invitations. Please try again later.</p>";
      });
}

// Handle accept/reject actions for show_invitations.html
function handleInvitationAction(thesisId, action) {
  fetch("handle_invitation.php", {
      method: "POST",
      headers: {
          "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `thesisId=${encodeURIComponent(thesisId)}&action=${encodeURIComponent(action)}`,
  })
      .then((response) => response.json())
      .then((data) => {
          if (data.error) {
              alert(`Error: ${data.error}`);
          } else if (data.success) {
              alert(data.success);
              location.reload(); // Reload to update the invitations list
          }
      })
      .catch((error) => {
          console.error("Error handling invitation action:", error);
          alert("An error occurred. Please try again.");
      });
}

// Wait for the DOM to load
document.addEventListener("DOMContentLoaded", function () {
  // Login button in index.html
  const loginNavButton = document.querySelector(".login-button");

  if (loginNavButton) {
    loginNavButton.addEventListener("click", function (event) {
      event.preventDefault();
      window.location.href = "login.html";
    });
  }
  // Login button in login.html
  const loginFormButton = document.getElementById("loginBtn");

  if (loginFormButton) {
    loginFormButton.addEventListener("click", function () {
      // Get input values
      const username = document.getElementById("username").value;
      const password = document.getElementById("password").value;

      if (!username || !password) {
        document.getElementById("message").innerText = "Please fill in both fields.";
        return;
      }

      // Send login data to the server
      fetch("validate_login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.redirect) {
            window.location.href = data.redirect; // Redirect to the appropriate page
          } else if (data.error) {
            document.getElementById("message").innerText = data.error;
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          document.getElementById("message").innerText = "An error occurred. Please try again.";
        });
    });
  }
});

/* Κουμπί 'Αρχική' */
document.addEventListener("DOMContentLoaded", function() {
  // Select the "Αρχική" button 
  const gotohomepageButton = document.getElementById("gotohomepageButton");

  // Add a click event listener
  if (gotohomepageButton) {
      gotohomepageButton.addEventListener("click", function (event) {
        event.preventDefault();	// Prevent the default anchor behavior
        window.location.href = "professor_main.html";	// Redirect to main page
    });
  }
});

/* Κουμπί 'Δημιουργία Διπλωματικής' */
document.addEventListener("DOMContentLoaded", function () {
  // Select the "Δημιουργία Διπλωματικής" button
  const createThesisButton = document.getElementById("createThesisButton");

  // Add a click event listener
  if (createThesisButton) {
    createThesisButton.addEventListener("click", function (event) {
      event.preventDefault(); // Prevent the default anchor behavior
      window.location.href = "professor_theses.html"; // Redirect to the target page
    });
  }
});

/* Κουμπί 'Νέο Θέμα +' */
document.addEventListener("DOMContentLoaded", function () {
  const addThesisButton = document.getElementById("addThesisBtn");

  if (addThesisButton) {
    addThesisButton.addEventListener("click", function () {
      window.location.href = "new_thesis.html"; // Redirect to the new thesis page
    });
  }
});

/* Κουμπί 'Ακύρωση' στο new_thesis.html */ 
document.addEventListener("DOMContentLoaded", function () {
  const cancelButton = document.getElementById("cancelButton");

  if (cancelButton) {
    cancelButton.addEventListener("click", function () {
      window.location.href = "professor_theses.html"; // Redirect to theses page
    });
  }
});

/* Κουμπί 'Υποβολή' στο new_thesis.html */

/* Κουμπί 'Ανάθεση Διπλωματικής' */
document.addEventListener("DOMContentLoaded", function() {
  // Select the "Ανάθεση Διπλωματικής" button
  const assignThesisButton = document.getElementById("assignThesisButton");

  // Add a click event listener
  if (assignThesisButton) {
    assignThesisButton.addEventListener("click", function (event){
      event.preventDefault(); // Prevent the default anchor behavior
      window.location.href = "assign_thesis.html"; // Redirect to assign_thesis.html
    });
  }
});

/* Κουμπί 'Προβολή Διπλωματικών' */
document.addEventListener("DOMContentLoaded", function() {
  // Select the "Προβολή Διπλωματικών" button 
  const showThesesButton = document.getElementById("showThesesButton");

  // Add a click event listener
  if (showThesesButton) {
    showThesesButton.addEventListener("click", function (event) {
        event.preventDefault();	// Prevent the default anchor behavior
        window.location.href = "show_theses.html";	// Redirect to show_theses.html
    });
  }
});

/* Κουμπί 'Προσκλήσεις σε Επιτροπή' */
document.addEventListener("DOMContentLoaded", function() {
  // Select the "Προσκλήσεις σε Επιτροπή" button
  const showInvitationsButton = document.getElementById("showInvitationsButton");

  // Add a click event listener
  if (showInvitationsButton) {
    showInvitationsButton.addEventListener("click", function (event){
      event.preventDefault(); // Prevent the default anchor behavior
      window.location.href = "show_invitations.html"; // Redirect to show_invitations.html
    });
  }
});

/* Κουμπί 'Διαχείρηση Διπλωματικών' */
document.addEventListener("DOMContentLoaded", function() {
  // Select the "Διαχείρηση Διπλωματικών" button
  const manageThesesButton = document.getElementById("manageThesesButton");

  // Add a click event listener
  if (manageThesesButton) {
    manageThesesButton.addEventListener("click", function (event){
      event.preventDefault(); // Prevent the default anchor behavior
      window.location.href = "manage_theses.html"; // Redirect to show_invitations.html
    });
  }
});

/* Κουμπί 'Αποσύνδεση' */
document.addEventListener("DOMContentLoaded", function () {
  const logoutButton = document.getElementById("logoutButton");

  if (logoutButton) {
      logoutButton.addEventListener("click", function (event) {
          event.preventDefault(); // Prevent the default anchor behavior

          const confirmLogout = confirm("Είστε σίγουρος ότι θέλετε να αποσυνδεθείτε;");
          if (confirmLogout) {
              fetch("logout.php", {
                  method: "POST",
                  headers: {
                      "Content-Type": "application/x-www-form-urlencoded",
                  },
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      window.location.href = "index.html"; // Redirect to the main page
                  } else {
                      alert("Αποτυχία αποσύνδεσης. Δοκιμάστε ξανά.");
                  }
              })
              .catch(error => {
                  console.error("Error during logout:", error);
              });
          }
      });
  }
});
