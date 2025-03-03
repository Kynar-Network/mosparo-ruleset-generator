# Mosparo Ruleset Generator

---

## Introduction

Welcome to the **Mosparo Ruleset Generator**! This project is a tool designed to simplify the creation and management of rulesets for the [Mosparo spam protection tool](https://mosparo.io/) and its [Github](https://github.com/mosparo/mosparo). Whether you're a developer, website administrator, or security enthusiast, this generator provides an intuitive interface to streamline the process of setting up and updating rulesets for enhanced form protection.

---

## Why the Project is Made

The primary goal of the **Mosparo Ruleset Generator** is to democratize ruleset creation for Mosparo. Traditionally, creating a comprehensive set of rules for spam protection tool can be time-consuming and technically challenging. This project aims to make that process more accessible by providing an easy-to-use web interface.

1. **Simplification**: Streamline the creation of complex rulesets with just a few clicks.
2. **User-Friendly Interface**: Intuitive design makes it accessible for users without extensive technical knowledge.
3. **Modular Rules**: Easily add, modify, or extend existing rules based on your specific requirements.
4. **Validation and Error Handling**: Built-in validation ensures the accuracy of your ruleset files.
5. **Progress Tracking**: Real-time progress updates provide transparency during the generation process.
6. **File Management**: Efficient handling of uploaded and generated files with automated cleanup options.
7. **Open Source Contribution**: Encourage community contributions to improve and expand functionality.
8. **Community Support**: Foster a supportive community for users to share knowledge and best practices.

---

## How to Use

### Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/StrangerGithuber/mosparo-ruleset-generator.git
   cd mosparo-ruleset-generator
   ```

2. **Configure Environment Variables**:
   Copy or rename the `.env.example` file to `.env` and configure your environment variables as needed.
   ```bash
   cp .env.example .env
   ```

   The .env file is mandatory and needs to be present. If you prefer not to configure it manually, the website will take care of the configuration automatically the first time you access it. 

3. **Install Dependencies**:
   Ensure you have Composer installed, then run:
   ```bash
   composer install
   ```

4. **Start the Symfony Server**:
   Use the built-in Symfony server to test locally.
   ```bash
   symfony serve
   ```
   Alternatively, you can use a web server like Apache or Nginx. Ensure the `public/` directory is set as the web root.

### Usage

1. **Access the Generator**:
   Open your browser and navigate to the URL where the Symfony server is running (e.g., `http://localhost:8000`). Otherwise navigate to your domain where is the webserver runs the project.

2. **Create a New JSON File or Upload an Existing One**:
   - **Create New**: Start from scratch by clicking the "Create New JSON File" button.
   - **Upload Existing**: Modify or extend an existing ruleset by uploading a JSON file.

3. **Configure Settings**:
   Fill out the form with your desired settings, such as rule name, description, type, spam rating factor, and more.

4. **Process Generation**:
   Click "Next" to start the generation process. You'll see real-time progress updates and system logs in different consoles.

5. **Download Generated Files**:
   Once the process is complete, download the generated JSON file and its SHA-256 hash for use with Mosparo.


### TXT File Upload Support

The generator supports uploading a `.txt` file to add entries directly to your ruleset. The format for the `.txt` file should be:

```
"value1", "value2"
```

Where:
- `"value1"` is the real value, such as an IP address or email.
- `"value2"` is the rating score (a numerical value).

For example, with emails:

```
"test@test.com", "10"
"test2@test.com", "2"
"test3@test.com", "20"
etc...
```

### Cronjob for Cleanup

To automatically delete temporary files from previous days, set up a cron job:

1. **Edit Crontab**:
   ```bash
   crontab -e
   ```

2. **Add the Following Line** (adjust the path as necessary):
   ```bash
   0 0 * * * /<path to the project>/bin/console app:delete-yesterday-temp-directory
   ```
   This cron job runs daily at midnight to clean up old temporary files. If you are using shared hosting then you need to give the path of the PHP too. Check with your webhosting how to do that.

   Here is an example:
   ```bash
   /usr/bin/php8.4 /home/rulesetgen/htdocs/bin/console app:delete-yesterday-temp-directory
   ```

### Understanding the Source Code

- **Frontend**: The frontend is built using Twig templates, providing a user-friendly interface. You can find these in the `templates` directory.
- **Backend**: The backend logic is handled by Symfony controllers and services. Key components include:
  - `ApiController.php`: Handles API requests for ruleset generation, validation, and progress tracking.
  - `TempDirectoryService.php`: Manages temporary directories for file storage.
- **Static Assets**: Located in the `public` directory, including CSS, JavaScript, and images.

### Future Plans

- Add options in the settings to define which column from the TXT file should be added to which section of the JSON file.
- Add support for CSV files.

---

## License

The **Mosparo Ruleset Generator** is free to use and modify under the terms of its [LICENSE](./LICENSE). Here are some key points:

1. **Free Usage**: You are welcome to use this project for any purpose.
2. **No Commercial Sale**: It is strictly forbidden to sell or monetize this code in any way.
3. **Open Contribution**: Contributions from the community are encouraged and appreciated.
4. **Respect Copyrights**: Always respect the original authors' rights when using or modifying the code.

---


# Support Kynar Network

If you find my work helpful, please consider supporting me:

[![Patreon](https://img.shields.io/badge/Patreon-F96854?style=for-the-badge&logo=patreon&logoColor=white)](https://patreon.com/KynarNetwork)
[![Ko-fi](https://img.shields.io/badge/Ko--fi-29ABE0?style=for-the-badge&logo=ko-fi&logoColor=white)](https://ko-fi.com/kynarnetwork)
[![BuyMeACoffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-FFDD00?style=for-the-badge&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/kynarnetwork)
[![Coinbase](https://img.shields.io/badge/Coinbase-0052FF?style=for-the-badge&logo=coinbase&logoColor=white)](https://commerce.coinbase.com/checkout/d07a3a58-435a-4827-adeb-8f0214d460d3)

---

# Final Thoughts

Thank you for choosing the **Mosparo Ruleset Generator** for your needs! This project is a collaborative effort aimed at making form protection more accessible and efficient. We hope it serves as a valuable tool in your arsenal against spam and automated attacks.

1. **User Support**: Feel free to reach out with any questions or feedback.
2. **Community Engagement**: Join our community to share ideas, report issues, and contribute improvements.
3. **Continuous Improvement**: Regular updates will bring new features and bug fixes based on user feedback.
4. **Documentation**: Comprehensive documentation ensures you can understand and customize the project as needed.
5. **Security First**: Our focus is on providing a secure and reliable tool for form protection.
6. **Flexibility**: The modular design allows easy adaptation to various use cases and environments.
7. **Accessibility**: Designed with an intuitive interface, making it accessible to users of all skill levels.
8. **Open Source Values**: We believe in open collaboration and the power of community-driven development.
9. **Gratitude**: A big thank you to all contributors and users for their support and contributions.
10. **Future Plans**: Exciting developments are on the horizon, including additional features and improvements based on user feedback.

We look forward to your continued support and contributions to make this project even better! Happy coding!