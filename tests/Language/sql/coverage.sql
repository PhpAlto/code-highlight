-- Single line comment
/* Multi-line
   comment */
SELECT 'string with \n escape', "quoted ""id"" identifier", `backtick ``id`` identifier`
FROM table
WHERE col = 1.5 AND @var = COUNT(x);
SELECT ! FROM tbl; -- Unknown character test
SELECT 'unclosed string
# unknown char
/* unclosed comment
