document.addEventListener("DOMContentLoaded", function () {
  // Fetch theses only if on professor_theses.html
  if (document.querySelector(".thesis-container")) {
    fetchTheses();
  }
  else if(document.querySelector(".announcements-container")) {
    fetchAnnouncements();
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
					  <h2>${announcement.ann_title}</h2>
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
function fetchTheses() {
  const thesisList = document.getElementById("thesisList");

  // Fetch data from the server
  fetch("fetch_theses.php")
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
        html += `
          <div class="thesis-item">
            <h2><strong>Θέμα:</strong>${thesis.th_title}</h2>
            <p><strong>Κατάσταση:</strong> ${thesis.th_status}</p>
            <p><strong>Φοιτητής/α:</strong>${thesis.th_student_email}</p>
            <p><strong>Περιγραφή:</strong>${thesis.th_description}</p>
          </div>
        `;
      });

      thesisList.innerHTML = html;
    })
    .catch((error) => {
      console.error("Error fetching theses:", error);
      thesisList.innerHTML = "<p>Failed to load theses. Please try again later.</p>";
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