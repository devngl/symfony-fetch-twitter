# Symfony 3.4 Applicant - Fetch twitter user tweets
---

* Tweets are stored in cache to avoid Twitter API limits and improve performance.
* 8 tests, 19 assertions
* Estimated time spent: ~8h

#### DEPENDENCIES:
Bundles: FOSRestBundle & JMSSerializer
Used to establish connection with Twitter Oauth: 
https://github.com/J7mbo/twitter-api-php

#### FACED ISSUES:
The latest JMSSerializer version breaks FOSRestBundle, older version used: [GitHub Issue](https://github.com/FriendsOfSymfony/FOSRestBundle/issues/1955)
