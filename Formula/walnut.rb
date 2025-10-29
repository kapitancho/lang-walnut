# typed: false
# frozen_string_literal: true

class Walnut < Formula
  desc "Strongly-typed, interpreted programming language with compile-time type checking"
  homepage "https://github.com/walnut-lang/walnut"
  license "MIT"
  version "0.1.6"

  url "https://github.com/walnut-lang/walnut/releases/download/v#{version}/walnut.phar"
  sha256 "3c30b4b9bd2e9d7cb303d0f73fa8db324335f30cf90c43b26dda94fab9bb5cc2"

  depends_on "php" => :runtime

  def install
    bin.install "walnut.phar" => "walnut"
  end

  test do
    assert_match(/Walnut \d+\.\d+\.\d+/, shell_output("#{bin}/walnut --version"))
  end
end