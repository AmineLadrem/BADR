function getCookie(cookieName) {
    const name = cookieName + "=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const cookieArray = decodedCookie.split(';');
    for(let i = 0; i < cookieArray.length; i++) {
        let cookie = cookieArray[i];
        while (cookie.charAt(0) == ' ') {
            cookie = cookie.substring(1);
        }
        if (cookie.indexOf(name) == 0) {
            return cookie.substring(name.length, cookie.length);
        }
    }
    return "";
}

// Get the value of the 'nom' cookie
const userName = getCookie('nom');

// Display the user's name if it exists
if (userName) {
    document.getElementById('userWelcome').innerText = "Bienvenue, " + userName;
}

// Logout function
function logout() {
    // Clear the 'nom' cookie
    document.cookie = "nom=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    // Redirect to the logout page
    window.location.href = "index.php"; // Replace "logout.php" with your logout page URL
}