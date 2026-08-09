const users = [{ name: 'Ada', active: true }];
const names = users
  .filter(({ active }) => active)
  .map(({ name }) => name);
for (const name of names) {
  console.log(`Hello, ${name}!`);
}
export { names };
