WITH active_users AS (
  SELECT id, name
  FROM users
  WHERE active = TRUE
)
SELECT name, COUNT(*) AS projects
FROM active_users JOIN projects USING (id)
GROUP BY name ORDER BY projects DESC;
