package example;
import java.util.List;
record User(String name, boolean active) {}
final class Greeting {
    static List<User> active(List<User> users) {
        return users.stream().filter(User::active).toList();
    }
}
