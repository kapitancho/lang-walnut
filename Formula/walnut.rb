# typed: false
# frozen_string_literal: true

class Walnut < Formula
  desc "Strongly-typed, interpreted programming language with compile-time type checking"
  homepage "https://github.com/walnut-lang/walnut"
  license "MIT"
  version "0.3.9"

  url "https://github.com/walnut-lang/walnut/releases/download/v#{version}/walnut.phar"
  sha256 "752b54123a1cad09dc6e88aedab3fc9636d4b6a7e9688634c67065fa8e56b45f"

  depends_on "php" => :runtime

  def install
    bin.install "walnut.phar" => "walnut"
  end

  test do
    assert_match(/Walnut \d+\.\d+\.\d+/, shell_output("#{bin}/walnut --version"))
  end
end