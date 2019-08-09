workflow "Workflow" {
  on = "push"
  resolves = ["composer install", "phpunit test"]
}

action "composer install" {
  uses = "MilesChou/composer-action@master"
  args = "install --ansi --no-interaction --no-suggest"
}

action "phpunit test" {
  uses = "docker://kanti/buildy"
  needs = ["composer install"]
  args = "ls && composer test"
}
