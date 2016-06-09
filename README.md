# Calendar
OOP procedural consistent height calendar month generator in PHP.

The typical calendar is padded with some days of the previous month and some days of the next month, to provide a display of any month, with a _consistent number of rows_ in any month. E.g.:

            June 2016
    Mon Tue Wed Thu Fri Sat Sun
    30  31  1   2   3   4   5
    6   7   8   9   10  11  12
    13  14  15  16  17  18  19
    20  21  22  23  24  25  26
    27  28  29  30  1   2   3
    4   5   6   7   8   9   10

In above example, 30th and 31st of May, as well as the 1st until 10th of July have respectively been prepended and appended to the days of June. Like this, every month can similarly consistently be generated as a set of six rows. This class does it for you.
