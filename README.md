# mbinfo-pinfo WordPress plugin

Protein Information widget

## Using 

Place Protein Info Widget in sidebar.

    
## Management
    
Protein data are store in CSV file in mbinfo-data bucket.

Loading image meta data from GCS to wordpress


    wp mbinfo-pinfo load

For detail, check out:
    
    wp help mbinfo-pinfo
    
## Testing

Setup your WP plugin test system by running

    bash bin/install-wp-test.sh
    
Edit `WP_TESTS_DIR` path in tests/bootstrap.php, where is your phpunit installation folder.    

In the plugin folder, run `phpunit` unit test runner.    
    
    phpunit
    
