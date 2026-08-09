interface User { name: string; active: boolean }
const users: User[] = [{ name: 'Ada', active: true }];
const active = users.filter((user): boolean => user.active);
function greet(user: User): string {
  return `Hello, ${user.name}!`;
}
const message: string = greet(active[0]);
export { active, message };
