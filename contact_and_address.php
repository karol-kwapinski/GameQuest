<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.php");
    exit();
}

$host = 'localhost';
$db = 'store';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact_address'])) {
    $first_name = htmlspecialchars(trim($_POST['first_name']), ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars(trim($_POST['last_name']), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $phone_number = htmlspecialchars(trim($_POST['phone_number']), ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars(trim($_POST['country']), ENT_QUOTES, 'UTF-8');
    $street = htmlspecialchars(trim($_POST['street']), ENT_QUOTES, 'UTF-8');
    $building_number = htmlspecialchars(trim($_POST['building_number']), ENT_QUOTES, 'UTF-8');
    $apartment_number = htmlspecialchars(trim($_POST['apartment_number']), ENT_QUOTES, 'UTF-8');
    $postal_code = htmlspecialchars(trim($_POST['postal_code']), ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars(trim($_POST['city']), ENT_QUOTES, 'UTF-8');

    $contacts_query = "SELECT * FROM contact_address WHERE customer_id = ?";
    $stmt = $conn->prepare($contacts_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $contacts_result = $stmt->get_result();

    $is_default = ($contacts_result->num_rows == 0) ? 1 : 0;

    $add_query = "INSERT INTO contact_address (customer_id, first_name, last_name, email, phone_number, country, street, building_number, apartment_number, postal_code, city, is_default)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($add_query);
    $stmt->bind_param(
        'issssssssssi',
        $user_id,
        $first_name,
        $last_name,
        $email,
        $phone_number,
        $country,
        $street,
        $building_number,
        $apartment_number,
        $postal_code,
        $city,
        $is_default
    );
    $stmt->execute();

    header("Location: contact_and_address.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM contact_address WHERE id = $delete_id AND customer_id = $user_id";
    $conn->query($delete_query);
    header("Location: contact_and_address.php");
    exit();
}

if (isset($_GET['set_default'])) {
    $id = intval($_GET['set_default']);
    $conn->query("UPDATE contact_address SET is_default = 0 WHERE customer_id = $user_id");
    $conn->query("UPDATE contact_address SET is_default = 1 WHERE id = $id AND customer_id = $user_id");
    header("Location: contact_and_address.php");
    exit();
}

$contacts_query = "SELECT * FROM contact_address WHERE customer_id = $user_id";
$contacts_result = $conn->query($contacts_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact and Address Details</title>
    <link rel="stylesheet" href="styles_for_contact_and_address.css">
</head>
<body>
    <h1>Contact and Address Details</h1>

    <h2>Your Contact and Address Details:</h2>
    <?php if ($contacts_result->num_rows > 0): ?>
        <ul>
            <?php while ($row = $contacts_result->fetch_assoc()): ?>
                <li>
                    <p><strong>First Name:</strong> <?= htmlspecialchars($row['first_name']) ?></p>
                    <p><strong>Last Name:</strong> <?= htmlspecialchars($row['last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($row['phone_number']) ?></p>
                    <p><strong>Country:</strong> <?= htmlspecialchars($row['country']) ?></p>
                    <p><strong>Street:</strong> <?= htmlspecialchars($row['street']) ?></p>
                    <p><strong>Building Number:</strong> <?= htmlspecialchars($row['building_number']) ?></p>
                    <p><strong>Apartment Number:</strong> <?= htmlspecialchars($row['apartment_number']) ?></p>
                    <p><strong>Postal Code:</strong> <?= htmlspecialchars($row['postal_code']) ?></p>
                    <p><strong>City:</strong> <?= htmlspecialchars($row['city']) ?></p>
                    <p><strong>Default:</strong> <?= $row['is_default'] ? 'Yes' : 'No' ?></p>
                    <a href="contact_and_address.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete these details?')">Delete</a>
                    <a href="contact_and_address.php?set_default=<?= $row['id'] ?>">Set as Default</a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No details saved.</p>
    <?php endif; ?>

    <h2>Add Contact and Address Details:</h2>
    <h2>Add New Contact and Address Information:</h2>
    <form method="POST" action="contact_and_address.php" onsubmit="return validateForm()">
    <label for="first_name">First name:</label>
    <input type="text" id="first_name" name="first_name" required>
    <span id="first_name_error" class="error-message"></span>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" required>
    <span id="last_name_error" class="error-message"></span>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <span id="email_error" class="error-message"></span>

    <label for="phone_number">Phone number:</label>
    <input type="text" id="phone_number" name="phone_number" required>
    <span id="phone_number_error" class="error-message"></span>

    <label for="country">Country:</label>
    <input type="text" id="country" name="country" required>
    <span id="country_error" class="error-message"></span>

    <label for="street">Street:</label>
    <input type="text" id="street" name="street" required>
    <span id="street_error" class="error-message"></span>

    <label for="building_number">Building number:</label>
    <input type="text" id="building_number" name="building_number" required>
    <span id="building_number_error" class="error-message"></span>

    <label for="apartment_number">Apartment number:</label>
    <input type="text" id="apartment_number" name="apartment_number">
    <span id="apartment_number_error" class="error-message"></span>

    <label for="postal_code">Postal code:</label>
    <input type="text" id="postal_code" name="postal_code" required>
    <span id="postal_code_error" class="error-message"></span>

    <label for="city">City:</label>
    <input type="text" id="city" name="city" required>
    <span id="city_error" class="error-message"></span>

    <button type="submit" name="add_contact_address">Add Data</button>
</form>

<style>
    .error-message {
        color: red;
        font-size: 12px;
    }
</style>

<script>
const nameRegex = /^[A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]+$/;
const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
const phoneRegex = /^[0-9]{9,10}$/;
const countryStreetRegex = /^[A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]+$/;
const buildingNumberRegex = /^[A-Za-z0-9]{1,5}$/;
const apartmentNumberRegex = /^[0-9]{1,5}$/;
const postalCodeRegex = /^[0-9-]{1,8}$/;
const cityRegex = /^[A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]+$/;

function validateForm() {
    let valid = true;

    const firstName = document.getElementById('first_name').value.trim();
    const firstNameError = document.getElementById('first_name_error');
    if (!firstName || !nameRegex.test(firstName)) {
        firstNameError.textContent = "First name must contain only letters.";
        valid = false;
    } else {
        firstNameError.textContent = "";
    }

    const lastName = document.getElementById('last_name').value.trim();
    const lastNameError = document.getElementById('last_name_error');
    if (!lastName || !nameRegex.test(lastName)) {
        lastNameError.textContent = "Last name must contain only letters.";
        valid = false;
    } else {
        lastNameError.textContent = "";
    }

    const email = document.getElementById('email').value.trim();
    const emailError = document.getElementById('email_error');
    if (!email || !emailRegex.test(email)) {
        emailError.textContent = "Please enter a valid email address.";
        valid = false;
    } else {
        emailError.textContent = "";
    }

    const phoneNumber = document.getElementById('phone_number').value.trim();
    const phoneNumberError = document.getElementById('phone_number_error');
    if (!phoneNumber || !phoneRegex.test(phoneNumber)) {
        phoneNumberError.textContent = "Phone number must be 9 or 10 digits.";
        valid = false;
    } else {
        phoneNumberError.textContent = "";
    }

    const country = document.getElementById('country').value.trim();
    const countryError = document.getElementById('country_error');
    if (!country || !countryStreetRegex.test(country)) {
        countryError.textContent = "Country must contain only letters.";
        valid = false;
    } else {
        countryError.textContent = "";
    }

    const street = document.getElementById('street').value.trim();
    const streetError = document.getElementById('street_error');
    if (!street || !countryStreetRegex.test(street)) {
        streetError.textContent = "Street must contain only letters.";
        valid = false;
    } else {
        streetError.textContent = "";
    }

    const buildingNumber = document.getElementById('building_number').value.trim();
    const buildingNumberError = document.getElementById('building_number_error');
    if (!buildingNumber || !buildingNumberRegex.test(buildingNumber)) {
        buildingNumberError.textContent = "Building number must contain only letters and numbers, up to 5 characters.";
        valid = false;
    } else {
        buildingNumberError.textContent = "";
    }

    const apartmentNumber = document.getElementById('apartment_number').value.trim();
    const apartmentNumberError = document.getElementById('apartment_number_error');
    if (apartmentNumber && !apartmentNumberRegex.test(apartmentNumber)) {
        apartmentNumberError.textContent = "Apartment number must be digits, up to 5 digits.";
        valid = false;
    } else {
        apartmentNumberError.textContent = "";
    }

    const postalCode = document.getElementById('postal_code').value.trim();
    const postalCodeError = document.getElementById('postal_code_error');
    if (!postalCode || !postalCodeRegex.test(postalCode)) {
        postalCodeError.textContent = "Postal code must contain only digits and hyphen, up to 8 characters.";
        valid = false;
    } else {
        postalCodeError.textContent = "";
    }

    const city = document.getElementById('city').value.trim();
    const cityError = document.getElementById('city_error');
    if (!city || !cityRegex.test(city)) {
        cityError.textContent = "City must contain only letters.";
        valid = false;
    } else {
        cityError.textContent = "";
    }

    return valid;
}

document.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', validateForm);
});
</script>
    <a href="profile.php">Back to Profile</a>
</body>
</html>
