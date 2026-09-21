<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
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

    header("Location: checkout_page.php");
    exit();
}

$default_contact_query = "SELECT * FROM contact_address WHERE customer_id = $user_id AND is_default = 1 LIMIT 1";
$default_contact_result = $conn->query($default_contact_query);
$default_contact = $default_contact_result->fetch_assoc();

$current_contact_id = $default_contact['id'];

$contacts_query = "SELECT * FROM contact_address WHERE customer_id = $user_id";
$contacts_result = $conn->query($contacts_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../styles/styles_for_checkout.css">
    <script>
        let current_contact_id = <?= $current_contact_id ?>;

        function updateDisplayedContact(contact_id) {
            current_contact_id = contact_id;
            console.log(contact_id);
            const form_data = new FormData();
            form_data.append('contact_id', contact_id);
            fetch('../util/get_contact_details.php', {
                method: 'POST',
                body: form_data,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const contact_display = document.getElementById('defaultContactDisplay');
                    contact_display.innerHTML = `
                        <div>
                            <strong>${data.contact.first_name} ${data.contact.last_name}</strong><br>
                            Email: ${data.contact.email}<br>
                            Phone number: ${data.contact.phone_number}<br>
                            Address: ${data.contact.street} ${data.contact.building_number} ${data.contact.apartment_number != 0 ? '/' + data.contact.apartment_number : ''} ${data.contact.city}<br>
                            Postal code: ${data.contact.postal_code}, Country: ${data.contact.country}<br>
                        </div>
                    `;
                } else {
                    alert('Error: Unable to fetch contact details.');
                }
            })
            .catch(error => {
                console.error('Request error:', error);
                alert('There was an issue updating the data.');
            });
        }

        function toggleAddressList() {
            const address_list = document.getElementById('addressList');
            if (address_list.style.display === 'none' || address_list.style.display === '') {
                address_list.style.display = 'block';
            } else {
                address_list.style.display = 'none';
            }
        }

        function placeOrder() {
            console.log(current_contact_id);
            const form_data = new FormData();
            form_data.append('contact_id', current_contact_id);

            fetch('../util/place_order.php', {
                method: 'POST',
                body: form_data,
            })
            .then(response => {
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error('Invalid JSON: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.status === 'success') {
                    alert('The order was successfully placed!');
                    window.location.href = '../index.php';
                } else {
                    alert('There was an issue with the order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Order placing error:', error);
                alert('There was an issue with the order process.');
            });
        }
    </script>
</head>
<body>
    <h1>Checkout</h1>

    <h2>Selected Contact and Address Information:</h2>
    <div id="defaultContactDisplay">
        <?php if ($default_contact): ?>
            <div>
              <p><strong><?= htmlspecialchars($default_contact['first_name'] . ' ' . $default_contact['last_name']) ?></strong></p>
              <p>Email: <?= htmlspecialchars($default_contact['email']) ?></p>
              <p>Phone number: <?= htmlspecialchars($default_contact['phone_number']) ?></p>
              <p>Shipping Address: <?= htmlspecialchars($default_contact['street']) ?> <?= htmlspecialchars($default_contact['building_number']) ?><?= $default_contact['apartment_number'] != 0 ? ('/' . $default_contact['apartment_number']) : '' ?> <?= htmlspecialchars($default_contact['city']) ?></p>
              <p>Postal code: <?= htmlspecialchars($default_contact['postal_code']) ?>, Country: <?= htmlspecialchars($default_contact['country']) ?></p>
            </div>
        <?php else: ?>
            <p>You don't have a default contact and address set.</p>
        <?php endif; ?>
    </div>

    <button id="toggleButton" onclick="toggleAddressList()">Change Address and Contact</button>
    <div id="addressList" style="display: none;">
        <?php if ($contacts_result->num_rows > 0): ?>
            <ul>
                <?php while ($row = $contacts_result->fetch_assoc()): ?>
                    <li>
                        <button type="button" onclick="updateDisplayedContact(<?= $row['id'] ?>)">
                            <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong><br>
                            Email: <?= htmlspecialchars($row['email']) ?><br>
                            Phone number: <?= htmlspecialchars($row['phone_number']) ?><br>
                            Address: <?= htmlspecialchars($row['street']) ?> <?= htmlspecialchars($row['building_number']) ?><?= $row['apartment_number'] != 0 ? ('/' . $row['apartment_number']) : '' ?> <?= htmlspecialchars($row['city']) ?><br>
                            Postal code: <?= htmlspecialchars($row['postal_code']) ?>, Country: <?= htmlspecialchars($row['country']) ?><br>
                        </button>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You don't have any saved contact or address details.</p>
        <?php endif; ?>
    </div>

    <h2>Add New Contact and Address Information:</h2>
        <form method="POST" action="checkout_page.php" onsubmit="return validateForm()">
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

    <button type="button" onclick="placeOrder()">Place Order</button>
</body>
</html>
