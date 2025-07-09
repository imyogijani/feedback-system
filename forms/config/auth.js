// app.js (or similar client-side JavaScript file)

// Import the necessary Firebase SDKs
import { initializeApp } from "firebase/app";
import {
  getAuth,
  signInWithEmailAndPassword,
  onAuthStateChanged,
  signOut,
} from "firebase/auth";
import { getAnalytics } from "firebase/analytics"; // Optional, if you're using analytics

// Your web app's Firebase configuration (the details you provided)
const firebaseConfig = {
  apiKey: "AIzaSyDMTtRugW9lFa3ITfippO0DP7iSmGuiVRY",
  authDomain: "sign-in-549ee.firebaseapp.com",
  projectId: "sign-in-549ee",
  storageBucket: "sign-in-549ee.firebasestorage.app",
  messagingSenderId: "723595676696",
  appId: "1:723595676696:web:a0925f60f0cfc710cf876d",
  measurementId: "G-PCQDPTBF5Y",
};
// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app); // Get the Firebase Authentication service
const analytics = getAnalytics(app); // Initialize analytics (optional)

console.log("Firebase App Initialized!");

// --- Functions to handle user interactions ---

// Example: Function to handle user login
async function handleLogin(email, password) {
  console.log("Attempting to log in with:", email);
  try {
    // Sign in the user with email and password using Firebase Auth
    const userCredential = await signInWithEmailAndPassword(
      auth,
      email,
      password
    );
    const user = userCredential.user;
    console.log("User signed in successfully:", user.email);

    // Get the Firebase ID Token
    const idToken = await user.getIdToken();
    console.log("Generated Firebase ID Token:", idToken);

    // Send the ID token to your PHP backend for session creation
    await sendIdTokenToPhpBackend(idToken);
  } catch (error) {
    // Handle Firebase authentication errors
    const errorCode = error.code;
    const errorMessage = error.message;
    console.error("Firebase Login Error:", errorCode, errorMessage);
    alert(`Login failed: ${errorMessage}`); // Display error to the user
  }
}

// Function to send the ID token to your PHP backend
async function sendIdTokenToPhpBackend(idToken) {
  const phpEndpoint = "/api/login.php"; // <--- IMPORTANT: Adjust this path to your PHP script!
  // If your PHP file is directly in the web root, it might be '/login.php'
  // If it's in a subfolder like 'backend', it might be '/backend/login.php'

  console.log("Sending ID token to PHP backend at:", phpEndpoint);
  try {
    const response = await fetch(phpEndpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ idToken: idToken }), // Key MUST be 'idToken' to match PHP
    });

    const data = await response.json(); // Parse the JSON response from PHP
    console.log("Response from PHP backend:", data);

    if (data.success) {
      console.log("PHP backend session created successfully!");
      alert("Login successful! Welcome.");
      // You might redirect the user or update UI here
      // window.location.href = '/dashboard.html';
    } else {
      console.error("PHP backend reported an error:", data.message);
      alert(`Backend login failed: ${data.message}`);
      // Log additional debug info from PHP if available
      if (data.decoded_data) {
        console.error("PHP decoded data for debugging:", data.decoded_data);
      }
      if (data.raw_input) {
        console.error("PHP raw input for debugging:", data.raw_input);
      }
    }
  } catch (error) {
    console.error("Error communicating with PHP backend:", error);
    alert("Network error or server unavailable. Please try again.");
  }
}

// Example: Function to handle user logout
async function handleLogout() {
  try {
    await signOut(auth);
    console.log("User logged out from Firebase.");
    // Optionally, clear server-side session (you'd need a PHP logout endpoint for this)
    // For now, just a client-side alert
    alert("You have been logged out.");
    // Redirect to login page or update UI
    // window.location.href = '/login.html';
  } catch (error) {
    console.error("Error during logout:", error);
    alert(`Logout failed: ${error.message}`);
  }
}

// --- Optional: Attach these functions to HTML elements (example) ---
// Assuming you have an HTML form with id="loginForm" and inputs for email/password,
// and a button for logout with id="logoutButton"

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", (event) => {
      event.preventDefault(); // Prevent default form submission
      const email = loginForm.elements.email.value;
      const password = loginForm.elements.password.value;
      handleLogin(email, password);
    });
  }

  const logoutButton = document.getElementById("logoutButton");
  if (logoutButton) {
    logoutButton.addEventListener("click", handleLogout);
  }

  // Keep track of user's authentication state
  onAuthStateChanged(auth, (user) => {
    if (user) {
      console.log("Auth state changed: User is logged in:", user.email);
      // Update UI to show logged-in state
    } else {
      console.log("Auth state changed: No user logged in.");
      // Update UI to show logged-out state
    }
  });
});
