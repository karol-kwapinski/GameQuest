<?php
$contactId = $_GET['contact_id'] ?? null;

if ($contactId) {
    echo "Contact ID: " . htmlspecialchars($contactId);
} else {
    echo "Nie przesłano Contact ID.";
}
?>
