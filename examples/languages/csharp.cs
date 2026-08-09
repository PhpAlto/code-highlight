using System;
using System.Linq;
var users = new[] { new User("Ada", true) };
foreach (var user in users.Where(user => user.Active))
{
    Console.WriteLine($"Hello, {user.Name}!");
}
record User(string Name, bool Active);
