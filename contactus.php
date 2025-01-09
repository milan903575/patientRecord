<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    color: #333;
}

.contact-us-container {
    max-width: 900px;
    margin: 50px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

header {
    text-align: center;
    margin-bottom: 20px;
}

header h1 {
    font-size: 2.5rem;
    color: #2b6777;
}

header p {
    font-size: 1rem;
    color: #555;
}

.contact-content {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.form-section {
    flex: 1 1 50%;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #2b6777;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group input:focus, .form-group textarea:focus {
    border-color: #2b6777;
    outline: none;
}

button {
    display: inline-block;
    padding: 10px 20px;
    background: #2b6777;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
}

button:hover {
    background: #1a4c5d;
}

.map-section {
    flex: 1 1 50%;
}

.map-section h3 {
    margin-bottom: 10px;
    color: #2b6777;
}

iframe {
    width: 100%;
    height: 300px;
    border: none;
    border-radius: 10px;
}

.hidden {
    display: none;
    color: green;
    font-size: 1rem;
    margin-top: 10px;
    text-align: center;
}

</style>
</head>
<body>
    <div class="contact-us-container">
        <header>
            <h1>Contact Us</h1>
            <p>We would love to hear from you! Please fill out the form below.</p>
        </header>
        <div class="contact-content">
            <div class="form-section">
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Your Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" rows="4" placeholder="Write your message here" required></textarea>
                    </div>
                    <button type="submit">Send Message</button>
                </form>
                <p id="formMessage" class="hidden">Thank you for reaching out to us! We will respond soon.</p>
            </div>
            <div class="map-section">
                <h3>Our Location</h3>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d27365.618215789735!2d74.80727690790172!3d13.353656849458497!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bbca4a7d2c4edb7%3A0x8d588d4fb81d861f!2sManipal%20Institute%20of%20Technology!5e0!3m2!1sen!2sin!4v1732144804545!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

            </div>
        </div>
    </div>
    <script>

document.getElementById('contactForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formMessage = document.getElementById('formMessage');
    formMessage.classList.remove('hidden');
    this.reset();
});

</script>
</body>
</html>
