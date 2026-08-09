struct User { name: String, active: bool }
fn active_name(user: &User) -> Option<&str> {
    user.active.then_some(user.name.as_str())
}
fn main() {
    let user = User { name: "Ada".into(), active: true };
    println!("{:?}", active_name(&user));
}
