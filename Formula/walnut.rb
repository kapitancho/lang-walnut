# typed: false
# frozen_string_literal: true

class Walnut < Formula
  desc "Strongly-typed, interpreted programming language with compile-time type checking"
  homepage "https://github.com/walnut-lang/walnut"
  license "MIT"
  version "0.1.5"

  url "https://github.com/walnut-lang/walnut/releases/download/v#{version}/walnut.phar"
  sha256 "71ccc565cfb33ce335c588b032131349f05c54eb4fecbc433f2f9009c4a9e0e1"

  depends_on "php" => :runtime

  def install
    bin.install "walnut.phar" => "walnut"
  end

  test do
    assert_match(/Walnut \d+\.\d+\.\d+/, shell_output("#{bin}/walnut --version"))
  end
end