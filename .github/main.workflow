workflow "Workflow" {
  on = "push"
  resolves = ["phpunit test"]
}

action "composer install" {
  uses = "docker://kanti/buildy"
  args = "composer install --ignore-platform-reqs --no-interaction --no-suggest"
}

action "phpunit test" {
  uses = "docker://kanti/buildy"
  needs = ["composer install"]
  args = "ls && composer test"
}
