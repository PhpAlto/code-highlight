package main
import "fmt"
const app = "Alto"
type User struct { Name string }
func (u User) Greet() string {
	return fmt.Sprintf("%s: Hello, %s!", app, u.Name)
}
func main() { fmt.Println(User{Name: "Ada"}.Greet()) }
