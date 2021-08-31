# Psr-6 In-Memory Cache

[Psr-6](https://www.php-fig.org/psr/psr-6/) compliant In-Memory cache implementation using an array as storage. <br/>
This implementation is usefull for testing purposes and per-request caching.

Because the library internally stores its data in an array, all data is lost after the php process is terminated (most likely when your request finishes).
