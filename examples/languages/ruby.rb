User = Data.define(:name, :active)
users = [
  User.new(name: "Ada", active: true),
  User.new(name: "Grace", active: false)
]
users.select(&:active).each do |user|
  puts "Hello, #{user.name}!"
end
