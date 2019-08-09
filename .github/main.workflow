workflow "Workflow" {
  resolves = [
    "composer install",
  ]
  on = "push"
}

action "setup docker" {
  needs = ["composer install"]
  uses = "docker://kanti/buildy"
  args = "composer test"
}

action "composer install" {
  uses = "MilesChou/composer-action@master"
  args = "install -q --no-ansi --no-interaction --no-scripts --no-suggest --no-progress"
}
