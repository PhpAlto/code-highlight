struct User {
    let name: String
    var greeting: String { "Hello, \(name)!" }
}
let users = [User(name: "Ada"), User(name: "Grace")]
for user in users {
    print(user.greeting)
}
