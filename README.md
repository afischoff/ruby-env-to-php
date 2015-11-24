# ruby-env-to-php
Allows a PHP codebase to use an existing Ruby ".env" configuration file. This allows both codebases to share the same configuration.

Usage:

    <?php
    use Afischoff\RubyEnvToPhp;
    
    require ('src/Afischoff/RubyEnvToPhp.php');
    
    // load Ruby .env file
    RubyEnvToPhp::load('../path/to/ruby/codebase/.envFile');
    
    // convert DB connection string to Laravel/Lumen DB environment variables
    RubyEnvToPhp::splitDbStringToVars($_ENV['DATABASE_URL'], 'mysql');


Adding to Laravel / Lumen:
Toward the top of "bootstrap/app.php":

    \Afischoff::RubyEnvToPhp::load('../path/to/ruby/codebase/.envFile');