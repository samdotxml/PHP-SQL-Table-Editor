# MySQL Table Editor
![Image](https://miro.medium.com/max/640/1*P0fA0UPF3La8bbd3gprBTQ.png)

## Description
We had to create a simple MySQL Table Editor for M151.

<br>

## How it works
You need to copy the `index.php`. How you create the mysqli connection is up to you. The class takes it as an argument for the `__construct` method. There is also an example in the `index.php` for input validation.

There is a method called `printTables`. This is the actual Table Editor. The logic behind it is fairly easy. For each defined table, a HTML-Table gets printed out. Each table row is in a form and the `<td></td>` elements have a input in between.

The Editor will not work if you have a composite key in your defined tables!

<br>

## Support
There is no support for this editor. I only uploaded it to github for version control and backup purposes