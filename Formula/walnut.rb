# typed: false
# frozen_string_literal: true

class Walnut < Formula
  desc "Strongly-typed, interpreted programming language with compile-time type checking"
  homepage "https://github.com/walnut-lang/walnut"
  license "MIT"
  version "0.3.10"

  url "https://github.com/walnut-lang/walnut/releases/download/v#{version}/walnut.phar"
  sha256 "e50802a915683cb00ecccd4f714130f5d41b495370c8498f822a9356f151f3a9"

  depends_on "php" => :runtime

  def install
    bin.install "walnut.phar" => "walnut"
  end

  test do
    assert_match(/Walnut \d+\.\d+\.\d+/, shell_output("#{bin}/walnut --version"))
  end
end