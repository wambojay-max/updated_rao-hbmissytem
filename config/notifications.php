<?php
function queueBookingNotification(PDO $pdo, int $bookingId, string $recipient, string $message): void
{
    $channel = filter_var($recipient, FILTER_VALIDATE_EMAIL) ? "email" : "sms";
    $stmt = $pdo->prepare("INSERT INTO booking_notifications (booking_id, channel, recipient, message) VALUES (:booking_id, :channel, :recipient, :message)");
    $stmt->execute(["booking_id" => $bookingId, "channel" => $channel, "recipient" => $recipient, "message" => $message]);
}
?>