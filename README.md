# mbinfo-pinfo WordPress plugin

Protein Information widget

## Using 

Place Protein Info Widget in sidebar.

Create a page and give the name protein. This page will be custom render for protein list and protein detail.

    
## Management
    
Protein data are store in CSV file in mbinfo-data bucket.

To load protein information data

    gsutil cp ./yourfile.csv gs://mbi-data/pinfo/pinfo.csv
    
Then load the file
    
    wp mbinfo-pinfo load

For detail, check out:
    
    wp help mbinfo-pinfo
    
## Testing

Setup your WP plugin test system by running

    bash bin/install-wp-test.sh
    
Edit `WP_TESTS_DIR` path in tests/bootstrap.php, where is your phpunit installation folder. 
   
Load data before running the test, `wp mbinfo-pinfo load`.    

In the plugin folder, run `phpunit` unit test runner.    
    
    phpunit
    
