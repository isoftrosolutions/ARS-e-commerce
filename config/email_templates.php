

<?php
/**
 * config/email_templates.php
 * Professional Email Templates for ARS Shop
 */

return [
    /* -------------------------------------------------------------------------- */
    /* 1. OTP / VERIFICATION CODE EMAIL                                           */
    /* -------------------------------------------------------------------------- */
    'otp_email' => [
        'subject' => '{{otp}} is your ARS verification code',
        'content_html' => '
        <div style="background-color: #fdfaf7; padding: 40px 20px; font-family: sans-serif; color: #1a0e05; line-height: 1.6;">
            <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e4d9d0;">
                <!-- Header -->
                <div style="background: #130c06; padding: 30px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-family: serif; font-size: 24px; letter-spacing: 2px;">ARS<span style="color: #ea580c;">SHOP</span></h1>
                </div>
                
                <!-- Body -->
                <div style="padding: 40px 30px; text-align: center;">
                    <h2 style="font-family: serif; font-size: 28px; margin-bottom: 20px; color: #130c06;">Verification Code</h2>
                    <p style="color: #6b5c4e; font-size: 16px; margin-bottom: 30px;">Hello {{name}}, use the code below to securely verify your account. It will expire in 10 minutes.</p>
                    
                    <div style="background: #fdfaf7; border: 1.5px dashed #ea580c; border-radius: 12px; padding: 25px; display: inline-block; margin-bottom: 30px;">
                        <span style="font-family: serif; font-size: 42px; font-weight: bold; color: #ea580c; letter-spacing: 12px; margin-left: 12px;">{{otp}}</span>
                    </div>
                    
                    <p style="color: #a89688; font-size: 14px;">If you didn\'t request this code, you can safely ignore this email.</p>
                </div>
                
                <!-- Footer -->
                <div style="padding: 30px; background: #130c06; color: #a89688; font-size: 12px; text-align: center; border-top: 1px solid #2d1a0c;">
                    <p style="margin-bottom: 10px;">&copy; ' . date('Y') . ' ARS E-Commerce. All rights reserved.</p>
                    <p>Kathmandu, Nepal | Support: support@ars.com</p>
                </div>
            </div>
        </div>'
    ],

    /* -------------------------------------------------------------------------- */
    /* 2. PASSWORD RESET LINK EMAIL                                               */
    /* -------------------------------------------------------------------------- */
    'password_reset' => [
        'subject' => 'Reset your ARS password',
        'content_html' => '
        <div style="background-color: #fdfaf7; padding: 40px 20px; font-family: sans-serif; color: #1a0e05; line-height: 1.6;">
            <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e4d9d0;">
                <div style="background: #130c06; padding: 30px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-family: serif; font-size: 24px; letter-spacing: 2px;">ARS<span style="color: #ea580c;">SHOP</span></h1>
                </div>
                
                <div style="padding: 40px 30px;">
                    <h2 style="font-family: serif; font-size: 28px; margin-bottom: 20px; color: #130c06;">Account Recovery</h2>
                    <p style="color: #6b5c4e; font-size: 16px; margin-bottom: 25px;">Hi {{name}}, we received a request to reset your ARS account password. Click the button below to secure your account:</p>
                    
                    <div style="text-align: center; margin: 35px 0;">
                        <a href="{{reset_url}}" style="background: #ea580c; color: #ffffff; padding: 16px 32px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block;">Reset Password</a>
                    </div>
                    
                    <p style="color: #a89688; font-size: 13px;">Or copy and paste this link in your browser:<br><a href="{{reset_url}}" style="color: #ea580c; word-break: break-all;">{{reset_url}}</a></p>
                    <p style="color: #a89688; font-size: 13px; margin-top: 20px;">This link is valid for 1 hour. If you didn\'t request this, no action is needed.</p>
                </div>
                
                <div style="padding: 30px; background: #130c06; color: #a89688; font-size: 12px; text-align: center;">
                    <p style="margin-bottom: 10px;">&copy; ' . date('Y') . ' ARS E-Commerce. Nepal</p>
                </div>
            </div>
        </div>'
    ],

    /* -------------------------------------------------------------------------- */
    /* 3. WELCOME EMAIL                                                           */
    /* -------------------------------------------------------------------------- */
    'welcome_email' => [
        'subject' => 'Welcome to ARS, {{name}}!',
        'content_html' => '
        <div style="background-color: #fdfaf7; padding: 40px 20px; font-family: sans-serif; color: #1a0e05; line-height: 1.6;">
            <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e4d9d0;">
                <div style="background: #130c06; padding: 30px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-family: serif; font-size: 24px; letter-spacing: 2px;">ARS<span style="color: #ea580c;">SHOP</span></h1>
                </div>
                
                <div style="padding: 40px 30px;">
                    <h2 style="font-family: serif; font-size: 28px; margin-bottom: 20px; color: #130c06;">Welcome to the family.</h2>
                    <p style="color: #6b5c4e; font-size: 16px; margin-bottom: 20px;">Hi {{name}}, we re delighted to have you with us! Your account is now active and ready for your first shopping experience.</p>
                    
                    <div style="background: #fdfaf7; padding: 25px; border-radius: 12px; margin: 25px 0;">
                        <h3 style="margin: 0 0 10px; font-size: 14px; color: #ea580c; text-transform: uppercase;">Discover the Best</h3>
                        <p style="margin: 0; color: #130c06; font-size: 15px;">Explore our curated collection of electronics, fashion, and home essentials tailored just for Nepal.</p>
                    </div>

                    <div style="text-align: center; margin-bottom: 30px;">
                        <a href="' . env('SITE_URL') . '" style="background: #130c06; color: #ffffff; padding: 16px 32px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block;">Start Shopping</a>
                    </div>
                </div>
                
                <div style="padding: 30px; background: #130c06; color: #a89688; font-size: 12px; text-align: center;">
                    <p>&copy; ' . date('Y') . ' ARS E-Commerce. All rights reserved.</p>
                </div>
            </div>
        </div>'
    ],

    /* -------------------------------------------------------------------------- */
    /* 4. ORDER CONFIRMATION EMAIL                                                */
    /* -------------------------------------------------------------------------- */
    'order_confirmation' => [
        'subject' => 'Confirmed! Your order #{{order_id}} has been received',
        'content_html' => '
        <div style="background-color: #fdfaf7; padding: 40px 20px; font-family: sans-serif; color: #1a0e05; line-height: 1.6;">
            <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e4d9d0;">
                <div style="background: #130c06; padding: 30px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-family: serif; font-size: 24px; letter-spacing: 2px;">ARS<span style="color: #ea580c;">SHOP</span></h1>
                </div>
                
                <div style="padding: 40px 30px;">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <div style="width: 60px; height: 60px; background: rgba(34,197,94,0.1); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                            <span style="color: #22c55e; font-size: 32px;">✓</span>
                        </div>
                        <h2 style="font-family: serif; font-size: 28px; margin: 0; color: #130c06;">Order Confirmed</h2>
                        <p style="color: #a89688; font-size: 14px;">Order #{{order_id}}</p>
                    </div>

                    <p style="color: #6b5c4e; font-size: 16px;">Hi {{name}},</p>
                    <p style="color: #6b5c4e; font-size: 16px;">Great news! We have received your order and are currently preparing it for shipment. We will notify you once it s on its way.</p>
                    
                    <div style="border: 1px solid #e4d9d0; border-radius: 12px; padding: 20px; margin: 25px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: #a89688;">Total Amount:</span>
                            <span style="color: #130c06; font-weight: bold;">{{total}}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #a89688;">Status:</span>
                            <span style="color: #ea580c; font-weight: bold;">Processing</span>
                        </div>
                    </div>

                    <p style="color: #a89688; font-size: 14px;">If you have any questions, reply to this email or visit our support portal.</p>
                </div>
                
                <div style="padding: 30px; background: #130c06; color: #a89688; font-size: 12px; text-align: center;">
                    <p>&copy; ' . date('Y') . ' ARS E-Commerce. Nepal</p>
                </div>
            </div>
        </div>'
    ],

    /* -------------------------------------------------------------------------- */
    /* 5. PASSWORD RESET SUCCESS (SECURITY ALERT)                                 */
    /* -------------------------------------------------------------------------- */
    'password_reset_success' => [
        'subject' => 'Your ARS password has been changed',
        'content_html' => '
        <div style="background-color: #fdfaf7; padding: 40px 20px; font-family: sans-serif; color: #1a0e05; line-height: 1.6;">
            <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e4d9d0;">
                <div style="background: #130c06; padding: 30px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-family: serif; font-size: 24px; letter-spacing: 2px;">ARS<span style="color: #ea580c;">SHOP</span></h1>
                </div>
                
                <div style="padding: 40px 30px; text-align: center;">
                    <div style="width: 60px; height: 60px; background: rgba(234,88,12,0.1); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; margin: 0 auto 15px;">
                        <span style="color: #ea580c; font-size: 32px;">🛡️</span>
                    </div>
                    <h2 style="font-family: serif; font-size: 26px; margin-bottom: 20px; color: #130c06;">Security Alert</h2>
                    <p style="color: #6b5c4e; font-size: 16px; margin-bottom: 25px;">Hi {{name}}, this is to confirm that your password was successfully changed on <strong>{{date}}</strong>.</p>
                    
                    <p style="color: #a89688; font-size: 14px; background: #fdfaf7; padding: 15px; border-radius: 8px;">If you did not perform this action, please contact our support team immediately to secure your account.</p>
                </div>
                
                <div style="padding: 30px; background: #130c06; color: #a89688; font-size: 12px; text-align: center;">
                    <p>&copy; ' . date('Y') . ' ARS E-Commerce. Nepal</p>
                </div>
            </div>
        </div>'
    ],

    /* -------------------------------------------------------------------------- */
    /* 6. NEW CONTACT FORM MESSAGE (ADMIN NOTIFICATION)                           */
    /* -------------------------------------------------------------------------- */
    'new_contact_message' => [
        'subject' => 'New Contact Message from {{sender_name}}',
        'content_html' => '
        <div style="background-color:#fdfaf7;padding:40px 20px;font-family:sans-serif;color:#1a0e05;line-height:1.6;">
            <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.05);border:1px solid #e4d9d0;">
                <div style="background:#130c06;padding:30px;text-align:center;">
                    <h1 style="color:#ffffff;margin:0;font-family:serif;font-size:24px;letter-spacing:2px;">ARS<span style="color:#ea580c;">SHOP</span></h1>
                </div>
                <div style="padding:40px 30px;">
                    <h2 style="font-family:serif;font-size:26px;margin-bottom:20px;color:#130c06;">New Contact Message</h2>
                    <table style="width:100%;border-collapse:collapse;font-size:15px;">
                        <tr><td style="padding:10px 0;color:#a89688;width:100px;">From:</td><td style="padding:10px 0;color:#1a0e05;font-weight:600;">{{sender_name}}</td></tr>
                        <tr><td style="padding:10px 0;color:#a89688;">Email:</td><td style="padding:10px 0;"><a href="mailto:{{sender_email}}" style="color:#ea580c;">{{sender_email}}</a></td></tr>
                        <tr><td style="padding:10px 0;color:#a89688;">Subject:</td><td style="padding:10px 0;color:#1a0e05;">{{subject}}</td></tr>
                    </table>
                    <div style="background:#fdfaf7;border-left:3px solid #ea580c;padding:20px;margin-top:20px;border-radius:0 8px 8px 0;">
                        <p style="margin:0;color:#1a0e05;white-space:pre-wrap;">{{message}}</p>
                    </div>
                </div>
                <div style="padding:20px 30px;background:#130c06;color:#a89688;font-size:12px;text-align:center;">
                    <p style="margin:0;">&copy; ' . date('Y') . ' ARS E-Commerce. Nepal</p>
                </div>
            </div>
        </div>'
    ],
];
