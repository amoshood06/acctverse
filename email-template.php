<?php
/**
 * Email Template Generator for Purchase Notifications
 */

function getOrderConfirmationEmail($userName, $productName, $quantity, $price, $totalAmount, $orderId) {
    $emailTemplate = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f4f4f4;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .header {
                background-color: #007bff;
                color: white;
                padding: 20px;
                border-radius: 8px 8px 0 0;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content {
                padding: 20px 0;
            }
            .content p {
                margin: 10px 0;
            }
            .order-details {
                background-color: #f9f9f9;
                padding: 15px;
                border-left: 4px solid #007bff;
                margin: 20px 0;
            }
            .order-details h3 {
                margin-top: 0;
                color: #007bff;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid #eee;
            }
            .detail-row:last-child {
                border-bottom: none;
            }
            .detail-label {
                font-weight: bold;
                color: #555;
            }
            .detail-value {
                text-align: right;
                color: #333;
            }
            .total-row {
                background-color: #f0f0f0;
                padding: 10px;
                border-radius: 4px;
                font-weight: bold;
                display: flex;
                justify-content: space-between;
                margin-top: 10px;
            }
            .footer {
                text-align: center;
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 12px;
                color: #666;
            }
            .btn {
                display: inline-block;
                background-color: #007bff;
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header'>
                <h1>Order Confirmation</h1>
            </div>
            
            <div class='content'>
                <p>Dear <strong>{$userName}</strong>,</p>
                
                <p>Thank you for your purchase! Your order has been successfully processed. Here are your order details:</p>
                
                <div class='order-details'>
                    <h3>Order Information</h3>
                    <div class='detail-row'>
                        <span class='detail-label'>Order ID:</span>
                        <span class='detail-value'>#{$orderId}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Product:</span>
                        <span class='detail-value'>{$productName}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Quantity:</span>
                        <span class='detail-value'>{$quantity}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Price per Unit:</span>
                        <span class='detail-value'>\${$price}</span>
                    </div>
                    <div class='total-row'>
                        <span>Total Amount:</span>
                        <span>\${$totalAmount}</span>
                    </div>
                </div>
                
                <p>Your order is being processed and will be delivered shortly. You can track your order using Order ID: <strong>#{$orderId}</strong></p>
                
                <p>If you have any questions or concerns, please don't hesitate to contact our support team.</p>
                
                <p>Best regards,<br><strong>AcctVerse Team</strong></p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 AcctVerse. All rights reserved.</p>
                <p>This is an automated email. Please do not reply to this address.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return $emailTemplate;
}
?>
