workflow "Workflow" {
  on = "push"
  resolves = ["new-action"]
}

action "composer install" {
  uses = "MilesChou/composer-action@master"
  args = "install -q --no-ansi --no-interaction --no-scripts --no-suggest --no-progress"
}

action "phpunit test" {
  uses = "docker://kanti/buildy"
  needs = ["composer install"]
  args = "composer test"
}
