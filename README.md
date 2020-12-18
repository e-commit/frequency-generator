# Frequency generator

Get the next date (DateTime object) by frequency.

[![Build Status](https://travis-ci.com/e-commit/frequency-generator.svg?branch=master)](https://travis-ci.com/e-commit/frequency-generator)

## Installation ##

To install frequency-generator with Composer just run :

```bash
$ composer require ecommit/frequency-generator
```

## Usage ##

### Create generator ###

```php
use Ecommit\FrequencyGenerator\FrequencyGenerator;

$generator = new FrequencyGenerator();
```

### Frequency "every day" ###

```php
//Every day at 08:00:00 and 10:00:00
$dateTimeObject = $generator->nextInEveryDay(array(new \DateTime('10:00:00'), new \DateTime('08:00:00')));
```

Arguments :
* **array $times** Times (Array of DateTime objects). Default: only *00:00:00*


### Frequency "every week" ###

```php
//Every monday (at 08:00:00 and 10:00:00) and tuesday (at 08:00:00 and 10:00:00)
$dateTimeObject = $generator->nextInEveryWeek(array(1, 2), array(new \DateTime('06:00:00'), new \DateTime('08:00:00')));
```

Arguments :
* **array $days** Array of days in week (integers). (1=Monday => 7=Sunday). Default: Only *1* (monday)
* **array $times** Times (Array of DateTime objects). Default: only *00:00:00*


### Frequency "every month" ###

```php
//Every 1st (at 08:00:00 and 10:00:00) and 2nd (at 08:00:00 and 10:00:00)
$dateTimeObject = $generator->nextInEveryMonth(array(1, 2), array(new \DateTime('06:00:00'), new \DateTime('08:00:00')));
```

Arguments :
* **array $days** Array of days in month (integers). (1=>31). Default: Only *1* (1st)
* **array $times** Times (Array of DateTime objects). Default: only *00:00:00*


### Frequency "every quart" ###

```php
//Every 1st and 15th February, May, August and November (at 08:00:00 and 10:00:00)  
$dateTimeObject = $generator->nextInEveryQuart(array(2), array(1, 15), array(new \DateTime('06:00:00'), new \DateTime('08:00:00')));
```

Arguments :
* **array $monthOffsets** Array of month offsets in quart (integers). (1 = January, April, July, October. 2 = February, May, August, November. 3 = March, June, September, December). Default: Only *1* (January, April, July, October)
* **array $daysInMonth** Array of days in month (integers). (1=>31). Default: Only *1* (1st)
* **array $times** Times (Array of DateTime objects). Default: only *00:00:00*


### Frequency "every half year" ###

```php
//Every 1st and 15th February and August (at 08:00:00 and 10:00:00)  
$dateTimeObject = $generator->nextInEveryHalfYear(array(2), array(1, 15), array(new \DateTime('06:00:00'), new \DateTime('08:00:00')));
```


* **array $monthOffsets** Array of month offsets in half year (integers). 1 = January, July. 2 = February, August. 3 = March, September. 4 = April, October. 5 = May , November. 6 = June, December). Default: Only *1* (January, July)
* **array $daysInMonth** Array of days in month (integers). (1=>31). Default: Only *1* (1st)
* **array $times** Times (Array of DateTime objects). Default: only *00:00:00*


### Frequency "every year" ###

```php
//Every 1st and 15th January, February, March, April, May and June (at 08:00:00 and 10:00:00)  
$dateTimeObject = $generator->nextInEveryYear(array(1), array(1, 15), array(new \DateTime('06:00:00'), new \DateTime('08:00:00')));
```


* **array $monthOffsets** Array of month offsets in year (integers). (1 = January => 12 => December). Default: Only *1* (January)
* **array $daysInMonth** Array of days in month (integers). (1=>31). Default: Only *1* (1st)
* **array $times** Times (Array of DateTime objects). Default: only *00:00:00*


## License ##

This librairy is under the MIT license. See the complete license in *LICENSE* file.
