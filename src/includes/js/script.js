const users = []; // Mock database for storing user accounts

// Form containers
const selectionContainer = document.querySelector(".selection-container");
const loginFormContainer = document.getElementById("loginFormContainer");
const registrationFormContainer = document.getElementById("formContainer");
const successContainer = document.getElementById("successContainer");

// Buttons and forms
const createAccountBtn = document.getElementById("createAccountBtn");
const loginBtn = document.getElementById("loginBtn");
const backToMenu = document.getElementById("backToMenu");
const backToMenuFromRegister = document.getElementById("backToMenuFromRegister");
const goToLogin = document.getElementById("goToLogin");
const loginForm = document.getElementById("loginForm");
const registrationForm = document.getElementById("registrationForm");

// Navigation functions
function showContainer(show, hide) {
    hide.classList.add("hidden");
    show.classList.remove("hidden");
}

// Create account button
createAccountBtn.addEventListener("click", () => {
    showContainer(registrationFormContainer, selectionContainer);
});

// Back to menu buttons
backToMenu.addEventListener("click", () => showContainer(selectionContainer, loginFormContainer));
backToMenuFromRegister.addEventListener("click", () => showContainer(selectionContainer, registrationFormContainer));

// Login button
loginBtn.addEventListener("click", () => showContainer(loginFormContainer, selectionContainer));

// Clear button
document.getElementById("clearBtn").addEventListener("click", () => {
    registrationForm.reset();
});

// Registration form submission
registrationForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const birthdate = document.getElementById("birthdate").value;
    const gender = document.getElementById("gender").value;
    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const retypeEmail = document.getElementById("retypeEmail").value.trim();
    const password = document.getElementById("password").value;
    const retypePassword = document.getElementById("retypePassword").value;

    // Validation
    if (!/^[a-zA-Z]+$/.test(firstName) || !/^[a-zA-Z]+$/.test(lastName)) {
        alert("First and Last Name must contain alphabets only.");
        return;
    }

    const today = new Date();
    const dob = new Date(birthdate);
    const age = today.getFullYear() - dob.getFullYear();
    if (dob > today || age < 18) {
        alert("You must be 18 years old or above to register.");
        return;
    }

    if (!/^[a-zA-Z]+$/.test(username)) {
        alert("Username must contain alphabets only.");
        return;
    }

    if (!email.includes("@") || email !== retypeEmail) {
        alert("Emails do not match or are invalid.");
        return;
    }

    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password) || password !== retypePassword) {
        alert("Passwords must match and be at least 8 characters long, with uppercase, lowercase, numbers, and special characters.");
        return;
    }

    users.push({ username, password });
    alert("Your account has been successfully created.");
    showContainer(successContainer, registrationFormContainer);
});

// Login form submission
loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const loginUsername = document.getElementById("loginUsername").value.trim();
    const loginPassword = document.getElementById("loginPassword").value.trim();

    const user = users.find(user => user.username === loginUsername && user.password === loginPassword);
    if (user) {
        alert("Login successful!");
    } else {
        alert("No account found. Please create a new account.");
        showContainer(registrationFormContainer, loginFormContainer);
    }
});

// Success screen navigation
goToLogin.addEventListener("click", () => showContainer(loginFormContainer, successContainer));

// Reference for personalized username greeting
const welcomeUsername = document.getElementById("welcomeUsername");
const exploreMoreBtn = document.getElementById("exploreMoreBtn");

// Modified Login Success
loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const loginUsername = document.getElementById("loginUsername").value.trim();
    const loginPassword = document.getElementById("loginPassword").value.trim();

    const user = users.find(
        (user) => user.username === loginUsername && user.password === loginPassword
    );

    if (user) {
        alert("Login successful!");
        welcomeUsername.textContent = user.username; // Display username
        showContainer(welcomeContainer, loginFormContainer);
    } else {
        alert("No account found. Please create a new account.");
        showContainer(registrationFormContainer, loginFormContainer);
    }
});

// Explore Features Button
exploreMoreBtn.addEventListener("click", () => {
    alert("Stay tuned for upcoming features. We're building something amazing for you!");});