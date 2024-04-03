# Auditor API

Welcome to the Auditor API documentation! This API provides functionalities for auditing, managing jobs, and scheduling tasks.

## Introduction

The Auditor API is designed to facilitate auditing processes, managing jobs, and scheduling tasks within your Symfony application. Whether you need to track changes, monitor activities, or automate routine tasks, the Auditor API offers a robust set of features to meet your needs.

## Documentation

- [Auditor Documentation](Docs/auditor.md): This document provides detailed information about the Auditor module, including its functionalities and usage instructions.
- [Job Documentation](Docs/job.md): Learn how to manage jobs, monitor their progress, and retrieve job-related information using the Job module.
- [Schedule Documentation](Docs/schedule.md): Explore the scheduling capabilities of the Auditor API, including how to create, update, and delete schedules for automated tasks.

## Getting Started
# Setting up a Symfony Project Locally

## Cloning an Existing Symfony Project

1. **Clone the Project**:

2. **Navigate to Project Directory**:

3. **Install Dependencies**:
   - Symfony projects use Composer for managing dependencies. Run the following command to install the project dependencies:
     ```
     composer install
     ```

4. **Environment Configuration**: 
   - Edit the `.env` file to configure your local environment.

5. **Database Setup**:
   - Use Symfony's console command to do this:
     ```
     php bin/console doctrine:database:create
     php bin/console doctrine:migrations:migrate
     ```

6. **Development Server**:
   - Start the Symfony development server by running the following command:
   - This require Symfony cli installed
   - You can install Symfony
   - In Ubuntu
     ```
     wget https://get.symfony.com/cli/installer -O - | bash
     
     ```
   - In Macos
       ```
      wget https://get.symfony.com/cli/installer -O - | bash
       
       ```
   -In Windows
     ```
     scoop install symfony-cli
     
     ```      
   - Than run  
     ```
     symfony server:start
     ```
   This will start the server, and you'll see the URL where your Symfony application is running locally.

   -If you do not have a symfony cli install you can run the project by the following commnad:
     ```
     php -S localhost:8000 -t public/
     
     ```
     

