workflow "Workflow" {
  on = "push"
  resolves = [
    "composer install"
  ]
}

action "composer install" {
  uses = "MilesChou/composer-action@master"
  args = "install -q --no-ansi --no-interaction --no-scripts --no-suggest --no-progress"
}

ction "phpunit" {
  needs = ["composer install"]
  uses = "./actions/run-phpunit/"
  args = "tests/"
}
