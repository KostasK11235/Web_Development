// Επιλογή των στοιχείων φόρμας
const loginBtn = document.getElementById("loginBtn");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");
const messageElement = document.getElementById("message");

// Προσθήκη event listener στο κουμπί "Σύνδεση"
loginBtn.addEventListener("click", loginUser);

function loginUser() {
  // Λήψη τιμών από τα input
  const username = usernameInput.value.trim();
  const password = passwordInput.value.trim();

  // Απλό, στατικό παράδειγμα ελέγχου ταυτότητας
  if (username === "admin" && password === "1234") {
    messageElement.style.color = "green";
    messageElement.textContent = "Επιτυχής σύνδεση! Καλώς ήρθες, " + username;
  } else {
    messageElement.style.color = "red";
    messageElement.textContent = "Αποτυχία σύνδεσης! Δοκιμάστε ξανά.";
