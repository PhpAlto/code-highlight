from dataclasses import dataclass
@dataclass(frozen=True)
class User:
    name: str
def greet(user: User) -> str:
    return f"Hello, {user.name}!"
for user in [User("Ada"), User("Grace")]:
    print(greet(user))
