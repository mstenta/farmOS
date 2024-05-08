# Email configuration

farmOS needs to be able to send emails to users. This is used for password
reset emails, notifications, etc.

Depending on how you have farmOS deployed, there are a few ways to configure
your server to allow farmOS to send emails.

By default, farmOS will attempt to send emails via an SMTP server installed on
the same system. If you have [Postfix](http://www.postfix.org) installed, email
should work without any additional configuration, although they will most likely
be filtered as spam. [This StackOverflow topic](https://stackoverflow.com/questions/371/how-do-you-make-sure-email-you-send-programmatically-is-not-automatically-marked)
provides guidance for avoiding this.

## Docker

The [farmOS Docker images](/hosting/install#farmos-in-docker) do not include an
SMTP server, so you will see this error message when farmOS tries to send an
email:

> Unable to send e-mail. Contact the site administrator if the problem persists.

There are two potential solutions to this:

1. Use a third-party SMTP server as a relay. This can be achieved by adding the
   following code to your `settings.php` file. Replace the `host`, `port`,
   `user`, and `password` values with your SMTP relay credentials.

    ```php
    $config['system.mail']['interface']['default'] = 'symfony_mailer';
    $config['system.mail']['mailer_dsn'] = [
      'scheme' => 'smtp',
      'host' => 'smtp.example.com',
      'port' => 587,
      'user' => 'my-smtp-user',
      'password' => 'my-smtp-password',
    ];
    ```

2. Create your own Docker image which inherits from the farmOS image. This
   image can install an SMTP server like Postfix, which can be configured to
   send email directly, or relay it through another SMTP server.
